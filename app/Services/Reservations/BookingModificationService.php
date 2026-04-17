<?php

namespace App\Services\Reservations;

use App\Events\BookingModified;
use App\Jobs\EnviarMailReservaModificada;
use App\Models\Cliente;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\ReservaRecurso;
use App\Models\Servicio;
use App\Services\PolicyResolver;
use App\Support\ReservationChangeSummary;
use App\Tools\Data\ModifyBookingInput;
use App\Tools\Reservations\CreateQuoteTool;
use App\Tools\Reservations\SearchAvailabilityTool;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BookingModificationService
{
    public function __construct(
        private readonly SearchAvailabilityTool $searchAvailabilityTool,
        private readonly CreateQuoteTool $createQuoteTool,
        private readonly PolicyResolver $policyResolver,
    ) {}

    public function modify(ModifyBookingInput $input): array
    {
        $reserva = Reserva::query()
            ->where('negocio_id', $input->negocio_id)
            ->where('localizador', $input->locator)
            ->with(['negocio', 'servicio', 'cliente', 'recurso', 'estadoReserva', 'reservaRecursos.recurso'])
            ->first();

        if (! $reserva) {
            throw new RuntimeException('No se encontro ninguna reserva activa con ese localizador para este negocio.');
        }

        $this->assertModifiable($reserva);

        $negocio = $reserva->negocio ?? Negocio::find($reserva->negocio_id);
        if (! $negocio) {
            throw new RuntimeException('El negocio de la reserva ya no existe.');
        }

        $servicio = $this->resolveServicio($reserva, $input);
        $contact = $this->normalizeContactData($input, $reserva);
        $this->assertRequiredDocumentation($servicio, $contact);

        $slot = $this->resolveSlot($reserva, $input, $servicio);
        $pricing = $this->resolvePricing($reserva, $input, $servicio, $slot);
        $policy = $this->policyResolver->resolverParaReservaContexto($servicio, $negocio, $reserva);
        $cliente = $this->resolveCliente($reserva, $contact);
        $targetPartySize = $this->targetPartySize($input, $reserva);

        $before = $reserva->replicate();
        $before->setRelation('servicio', $reserva->servicio);
        $before->setRelation('cliente', $reserva->cliente);

        $preview = $this->buildPreview($reserva, $servicio, $contact, $slot, $pricing, $policy, $cliente, $targetPartySize);
        $changeSummary = ReservationChangeSummary::build($before, $preview);

        if ($changeSummary === []) {
            throw new RuntimeException('No se ha detectado ningun cambio real en la reserva.');
        }

        $updated = DB::transaction(function () use ($reserva, $servicio, $cliente, $contact, $slot, $pricing, $policy, $targetPartySize) {
            $locked = Reserva::query()
                ->whereKey($reserva->id)
                ->lockForUpdate()
                ->firstOrFail();

            $resourceIds = $this->extractResourceIds($slot);
            $primaryResourceId = $resourceIds[0] ?? data_get($slot, 'recurso_id');
            $sesionId = data_get($slot, 'sesion_id');

            $locked->fill([
                'servicio_id' => $servicio->id,
                'recurso_id' => $primaryResourceId,
                'sesion_id' => $sesionId,
                'cliente_id' => $cliente->id,
                'nombre_responsable' => $contact['name'],
                'email_responsable' => $contact['email'],
                'telefono_responsable' => $contact['phone'],
                'tipo_documento_responsable' => $contact['document_type'],
                'documento_responsable' => $contact['document_value'],
                'fecha' => (string) data_get($slot, 'fecha'),
                'hora_inicio' => $this->normalizeStoredTime((string) data_get($slot, 'hora_inicio')),
                'hora_fin' => $this->normalizeStoredTime((string) data_get($slot, 'hora_fin')),
                'numero_personas' => $targetPartySize,
                'precio_calculado' => $pricing['precio_calculado'],
                'notas' => $contact['notes'],
                'horas_minimas_cancelacion' => $policy['horas_minimas_cancelacion'],
                'permite_modificacion' => $policy['permite_modificacion'],
                'es_reembolsable' => $policy['es_reembolsable'],
                'porcentaje_senal' => $policy['porcentaje_senal'],
            ]);
            $locked->save();

            $cliente->fill([
                'nombre' => $contact['name'],
                'email' => $contact['email'],
                'telefono' => $contact['phone'],
            ]);
            $cliente->save();

            ReservaRecurso::query()->where('reserva_id', $locked->id)->delete();

            foreach ($resourceIds as $resourceId) {
                ReservaRecurso::create([
                    'reserva_id' => $locked->id,
                    'recurso_id' => $resourceId,
                    'fecha' => $locked->fecha,
                    'hora_inicio' => $locked->hora_inicio,
                    'hora_fin' => $locked->hora_fin,
                    'fecha_inicio_datetime' => $locked->inicio_datetime,
                    'fecha_fin_datetime' => $locked->fin_datetime,
                    'notas' => null,
                ]);
            }

            return $locked->fresh([
                'negocio',
                'servicio',
                'cliente',
                'recurso',
                'estadoReserva',
                'reservaRecursos.recurso',
            ]);
        });

        $this->sendModificationEmailIfNeeded($negocio, $updated, $changeSummary);
        $updated = $updated->fresh([
            'negocio',
            'servicio',
            'cliente',
            'recurso',
            'estadoReserva',
            'reservaRecursos.recurso',
        ]);
        BookingModified::dispatch($updated, $changeSummary);

        return [
            'booking' => $updated,
            'change_summary' => $changeSummary,
        ];
    }

    private function resolveServicio(Reserva $reserva, ModifyBookingInput $input): Servicio
    {
        $servicioId = $input->servicio_id ?? $reserva->servicio_id;

        $servicio = Servicio::query()
            ->where('negocio_id', $reserva->negocio_id)
            ->where('id', $servicioId)
            ->activos()
            ->first();

        if (! $servicio) {
            throw new RuntimeException('La experiencia indicada no existe o no esta activa para este negocio.');
        }

        return $servicio;
    }

    private function resolveSlot(Reserva $reserva, ModifyBookingInput $input, Servicio $servicio): array
    {
        if (! $this->requiresAvailabilityRefresh($input, $reserva, $servicio)) {
            $currentResourceIds = $reserva->reservaRecursos->pluck('recurso_id')->values()->all();

            if ($currentResourceIds === [] && $reserva->recurso_id !== null) {
                $currentResourceIds = [(int) $reserva->recurso_id];
            }

            return [
                'fecha' => $reserva->fecha?->toDateString(),
                'hora_inicio' => substr((string) $reserva->hora_inicio, 0, 5),
                'hora_fin' => substr((string) $reserva->hora_fin, 0, 5),
                'inicio_datetime' => optional($reserva->inicio_datetime)?->toDateTimeString(),
                'fin_datetime' => optional($reserva->fin_datetime)?->toDateTimeString(),
                'recurso_id' => $reserva->recurso_id,
                'recurso_ids' => $currentResourceIds,
                'sesion_id' => $reserva->sesion_id,
            ];
        }

        $availability = $this->searchAvailabilityTool->execute([
            'negocio_id' => $reserva->negocio_id,
            'servicio_id' => $servicio->id,
            'fecha' => $input->fecha ?? $reserva->fecha?->toDateString(),
            'numero_personas' => $this->targetPartySize($input, $reserva),
            'exclude_reserva_id' => $reserva->id,
        ]);

        if (! $availability->success) {
            throw new RuntimeException($availability->error ?? 'No se pudo comprobar la disponibilidad para la modificacion.');
        }

        $slots = data_get($availability->data, 'slots', []);
        if (! is_array($slots) || $slots === []) {
            throw new RuntimeException('No hay huecos disponibles para aplicar esa modificacion.');
        }

        $targetStart = $this->normalizeInputTime($input->hora_inicio) ?? substr((string) $reserva->hora_inicio, 0, 5);
        $targetEnd = $this->normalizeInputTime($input->hora_fin);
        $requestedResourceIds = $input->recurso_ids;
        $preferredCurrentResourceIds = $reserva->reservaRecursos->pluck('recurso_id')
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();

        if ($preferredCurrentResourceIds === [] && $reserva->recurso_id !== null) {
            $preferredCurrentResourceIds = [(int) $reserva->recurso_id];
        }

        $matches = collect($slots)
            ->filter(function (array $slot) use ($input, $targetStart, $targetEnd) {
                if ($input->slot_key !== null && ($slot['slot_key'] ?? null) !== $input->slot_key) {
                    return false;
                }

                if ($input->sesion_id !== null && (int) ($slot['sesion_id'] ?? 0) !== $input->sesion_id) {
                    return false;
                }

                if ($input->recurso_id !== null && (int) ($slot['recurso_id'] ?? 0) !== $input->recurso_id) {
                    return false;
                }

                if (($slot['hora_inicio'] ?? null) !== $targetStart) {
                    return false;
                }

                if ($targetEnd !== null && ($slot['hora_fin'] ?? null) !== $targetEnd) {
                    return false;
                }

                return true;
            })
            ->values();

        if ($matches->isNotEmpty()) {
            if ($requestedResourceIds === []) {
                $preferred = $matches->first(fn (array $slot) => $this->resourceIdsFromSlot($slot) === $preferredCurrentResourceIds);
                if (is_array($preferred)) {
                    return $preferred;
                }
            } else {
                $preferred = $matches->first(function (array $slot) use ($requestedResourceIds) {
                    $slotIds = $this->resourceIdsFromSlot($slot);
                    sort($slotIds);
                    $requested = $requestedResourceIds;
                    sort($requested);

                    return $slotIds === $requested;
                });

                if (is_array($preferred)) {
                    return $preferred;
                }
            }

            return $matches->first();
        }

        $flexibleMatch = collect($slots)->first(function (array $slot) use ($targetStart, $servicio) {
            if ($targetStart === null) {
                return false;
            }

            $isFlexible = (bool) ($slot['accepts_start_time_within_slot'] ?? false) || (bool) $servicio->precio_por_unidad_tiempo;
            if (! $isFlexible) {
                return false;
            }

            $start = $slot['hora_inicio'] ?? null;
            $end = $slot['hora_fin'] ?? null;

            return $start !== null && $end !== null && $targetStart >= $start && $targetStart <= $end;
        });

        if (is_array($flexibleMatch)) {
            return $this->materializeFlexibleSlot($flexibleMatch, $targetStart, $servicio);
        }

        throw new RuntimeException('El nuevo hueco solicitado no coincide con la disponibilidad actual.');
    }

    private function resolvePricing(Reserva $reserva, ModifyBookingInput $input, Servicio $servicio, array $slot): array
    {
        if (! $this->requiresPricingRefresh($input, $reserva, $servicio, $slot)) {
            return [
                'precio_calculado' => number_format((float) $reserva->precio_calculado, 2, '.', ''),
            ];
        }

        $quote = $this->createQuoteTool->execute([
            'negocio_id' => $reserva->negocio_id,
            'servicio_id' => $servicio->id,
            'numero_personas' => $this->targetPartySize($input, $reserva),
            'inicio_datetime' => data_get($slot, 'inicio_datetime'),
            'fin_datetime' => data_get($slot, 'fin_datetime'),
        ]);

        if (! $quote->success) {
            throw new RuntimeException($quote->error ?? 'No se pudo recalcular el precio de la reserva.');
        }

        return [
            'precio_calculado' => data_get($quote->data, 'precio_calculado', number_format((float) $servicio->precio_base, 2, '.', '')),
        ];
    }

    private function resolveCliente(Reserva $reserva, array $contact): Cliente
    {
        if ($reserva->cliente) {
            return $reserva->cliente;
        }

        $cliente = null;

        if ($contact['email'] !== null || $contact['phone'] !== null) {
            $cliente = Cliente::query()
                ->where(function ($query) use ($contact) {
                    $hasCondition = false;

                    if ($contact['email'] !== null) {
                        $query->whereRaw('LOWER(email) = ?', [mb_strtolower($contact['email'], 'UTF-8')]);
                        $hasCondition = true;
                    }

                    if ($contact['phone'] !== null) {
                        $method = $hasCondition ? 'orWhere' : 'where';
                        $query->{$method}('telefono', $contact['phone']);
                    }
                })
                ->first();
        }

        if ($cliente) {
            return $cliente;
        }

        return Cliente::create([
            'nombre' => $contact['name'],
            'email' => $contact['email'],
            'telefono' => $contact['phone'],
            'notas' => null,
        ]);
    }

    private function buildPreview(
        Reserva $reserva,
        Servicio $servicio,
        array $contact,
        array $slot,
        array $pricing,
        array $policy,
        Cliente $cliente,
        int $partySize
    ): Reserva {
        $preview = $reserva->replicate();
        $preview->exists = false;
        $preview->setRelation('servicio', $servicio);
        $preview->setRelation('cliente', $cliente);
        $preview->fill([
            'servicio_id' => $servicio->id,
            'cliente_id' => $cliente->id,
            'nombre_responsable' => $contact['name'],
            'email_responsable' => $contact['email'],
            'telefono_responsable' => $contact['phone'],
            'tipo_documento_responsable' => $contact['document_type'],
            'documento_responsable' => $contact['document_value'],
            'fecha' => (string) data_get($slot, 'fecha'),
            'hora_inicio' => $this->normalizeStoredTime((string) data_get($slot, 'hora_inicio')),
            'hora_fin' => $this->normalizeStoredTime((string) data_get($slot, 'hora_fin')),
            'numero_personas' => $partySize,
            'precio_calculado' => $pricing['precio_calculado'],
            'notas' => $contact['notes'],
            'horas_minimas_cancelacion' => $policy['horas_minimas_cancelacion'],
            'permite_modificacion' => $policy['permite_modificacion'],
            'es_reembolsable' => $policy['es_reembolsable'],
            'porcentaje_senal' => $policy['porcentaje_senal'],
        ]);
        $preview->sincronizarIntervalo();

        return $preview;
    }

    private function normalizeContactData(ModifyBookingInput $input, Reserva $reserva): array
    {
        $name = $this->collapseSpaces($input->contact_name) ?? $this->collapseSpaces($reserva->nombreResponsableEfectivo());
        $phone = $this->collapseSpaces($input->contact_phone) ?? $this->collapseSpaces($reserva->telefonoResponsableEfectivo());
        $email = $this->normalizeEmail($input->contact_email) ?? $this->normalizeEmail($reserva->emailResponsableEfectivo());
        $documentType = $this->collapseSpaces($input->document_type) ?? $this->collapseSpaces($reserva->tipo_documento_responsable);
        $documentValue = $this->collapseSpaces($input->document_value) ?? $this->collapseSpaces($reserva->documento_responsable);
        $notes = $this->collapseSpaces($input->notes) ?? $this->collapseSpaces($reserva->notas);

        if ($name === null) {
            throw new RuntimeException('Falta el nombre de la persona responsable de la reserva.');
        }

        if ($phone === null) {
            throw new RuntimeException('Falta el telefono de contacto para modificar la reserva.');
        }

        return [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'document_type' => $documentType,
            'document_value' => $documentValue,
            'notes' => $notes,
        ];
    }

    private function assertRequiredDocumentation(Servicio $servicio, array $contact): void
    {
        if ($servicio->documentacion_requerida === null || trim($servicio->documentacion_requerida) === '') {
            return;
        }

        if ($contact['document_type'] === null || $contact['document_value'] === null) {
            throw new RuntimeException('Esta experiencia requiere documentacion antes de guardar la modificacion. Necesito el tipo y el valor del documento.');
        }
    }

    private function assertModifiable(Reserva $reserva): void
    {
        if (in_array($reserva->estado_reserva_id, $this->estadosNoModificables(), true)) {
            throw new RuntimeException('Esta reserva ya no puede modificarse porque esta cancelada o marcada como no presentada.');
        }

        if (! $reserva->permite_modificacion) {
            throw new RuntimeException('La politica actual no permite modificar esta reserva.');
        }

        $inicio = $reserva->inicio_datetime
            ?? Carbon::parse($reserva->fecha?->toDateString().' '.substr((string) $reserva->hora_inicio, 0, 5).':00');

        if ($inicio->isPast()) {
            throw new RuntimeException('No se pueden modificar reservas de experiencias que ya han comenzado o han pasado.');
        }

        if ($reserva->horas_minimas_cancelacion !== null && $reserva->horas_minimas_cancelacion > 0) {
            $hoursUntil = now()->diffInHours($inicio, false);

            if ($hoursUntil < $reserva->horas_minimas_cancelacion) {
                throw new RuntimeException(
                    'Esta reserva ya no puede modificarse porque la politica exige al menos '
                    .$reserva->horas_minimas_cancelacion
                    .' horas de antelacion.'
                );
            }
        }
    }

    private function requiresAvailabilityRefresh(ModifyBookingInput $input, Reserva $reserva, Servicio $servicio): bool
    {
        return $input->servicio_id !== null
            || $input->fecha !== null
            || $input->hora_inicio !== null
            || $input->hora_fin !== null
            || $input->numero_personas !== null
            || $input->recurso_id !== null
            || $input->recurso_ids !== []
            || $input->slot_key !== null
            || $input->sesion_id !== null
            || $servicio->id !== $reserva->servicio_id;
    }

    private function requiresPricingRefresh(ModifyBookingInput $input, Reserva $reserva, Servicio $servicio, array $slot): bool
    {
        if ($servicio->id !== $reserva->servicio_id) {
            return true;
        }

        if ($input->numero_personas !== null && $input->numero_personas !== (int) $reserva->numero_personas) {
            return true;
        }

        if ($input->hora_inicio !== null || $input->hora_fin !== null || $input->fecha !== null) {
            return true;
        }

        if ($servicio->precio_por_unidad_tiempo) {
            return substr((string) $reserva->hora_inicio, 0, 5) !== (string) data_get($slot, 'hora_inicio')
                || substr((string) $reserva->hora_fin, 0, 5) !== (string) data_get($slot, 'hora_fin');
        }

        return false;
    }

    private function targetPartySize(ModifyBookingInput $input, Reserva $reserva): int
    {
        return $input->numero_personas ?? max(1, (int) $reserva->numero_personas);
    }

    private function resourceIdsFromSlot(array $slot): array
    {
        $resourceIds = data_get($slot, 'recurso_ids', []);

        if (! is_array($resourceIds) || $resourceIds === []) {
            $resourceId = data_get($slot, 'recurso_id');

            return is_numeric($resourceId) ? [(int) $resourceId] : [];
        }

        return collect($resourceIds)
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values()
            ->all();
    }

    private function extractResourceIds(array $slot): array
    {
        $resources = data_get($slot, 'recursos');

        if (is_array($resources) && $resources !== []) {
            $ids = collect($resources)
                ->pluck('id')
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($ids !== []) {
                return $ids;
            }
        }

        return $this->resourceIdsFromSlot($slot);
    }

    private function materializeFlexibleSlot(array $slot, string $requestedStart, Servicio $servicio): array
    {
        $fecha = (string) data_get($slot, 'fecha');
        $originalEnd = Carbon::parse($fecha.' '.data_get($slot, 'hora_fin').':00');
        $chosenStart = Carbon::parse($fecha.' '.$requestedStart.':00');
        $calculatedEnd = $chosenStart->copy()->addMinutes(max(1, (int) $servicio->duracion_minutos));
        $chosenEnd = $calculatedEnd->lessThanOrEqualTo($originalEnd) ? $calculatedEnd : $originalEnd;

        $slot['hora_inicio'] = $chosenStart->format('H:i');
        $slot['hora_fin'] = $chosenEnd->format('H:i');
        $slot['inicio_datetime'] = $chosenStart->toDateTimeString();
        $slot['fin_datetime'] = $chosenEnd->toDateTimeString();
        $slot['booking_time_mode'] = 'materialized_flexible_start';

        return $slot;
    }

    private function normalizeInputTime(?string $value): ?string
    {
        $value = $this->collapseSpaces($value);

        if ($value === null) {
            return null;
        }

        return substr($value, 0, 5);
    }

    private function normalizeStoredTime(string $value): string
    {
        $value = trim($value);

        return strlen($value) === 5 ? $value.':00' : $value;
    }

    private function normalizeEmail(?string $value): ?string
    {
        $value = $this->collapseSpaces($value);

        return $value !== null ? mb_strtolower($value, 'UTF-8') : null;
    }

    private function collapseSpaces(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim($value));

        return $value !== '' ? $value : null;
    }

    private function estadosNoModificables(): array
    {
        static $ids = null;

        if ($ids === null) {
            $ids = EstadoReserva::query()
                ->whereIn('nombre', ['Cancelada', 'No presentada'])
                ->pluck('id')
                ->all();
        }

        return $ids;
    }

    private function sendModificationEmailIfNeeded(Negocio $negocio, Reserva $reserva, array $changeSummary): void
    {
        if (! $negocio->mail_confirmacion_activo) {
            return;
        }

        if (! filled($reserva->emailResponsableEfectivo())) {
            return;
        }

        try {
            EnviarMailReservaModificada::dispatchSync($reserva, $changeSummary);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
