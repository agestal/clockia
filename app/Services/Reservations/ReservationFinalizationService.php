<?php

namespace App\Services\Reservations;

use App\Models\Cliente;
use App\Models\EstadoReserva;
use App\Models\Negocio;
use App\Models\Reserva;
use App\Models\ReservaRecurso;
use App\Models\Servicio;
use App\Services\PolicyResolver;
use App\Tools\Data\CreateBookingInput;
use App\Tools\Reservations\CreateQuoteTool;
use App\Tools\Reservations\SearchAvailabilityTool;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReservationFinalizationService
{
    public function __construct(
        private readonly SearchAvailabilityTool $searchAvailabilityTool,
        private readonly CreateQuoteTool $createQuoteTool,
        private readonly PolicyResolver $policyResolver,
    ) {}

    public function finalize(CreateBookingInput $input): Reserva
    {
        $negocio = Negocio::find($input->negocio_id);
        if (! $negocio) {
            throw new RuntimeException('El negocio indicado no existe.');
        }

        $servicio = Servicio::query()
            ->where('negocio_id', $negocio->id)
            ->where('id', $input->servicio_id)
            ->activos()
            ->first();

        if (! $servicio) {
            throw new RuntimeException('El servicio indicado no existe o no está activo para este negocio.');
        }

        $contact = $this->normalizeContactData($input);
        $this->assertRequiredDocumentation($servicio, $contact);

        $slot = $this->resolveSlot($input, $servicio);
        $cliente = $this->resolveCliente($contact);
        $policy = $this->policyResolver->resolverParaReservaContexto($servicio, $negocio);
        $pricing = $this->resolvePricing($input, $servicio, $slot);
        $estadoReservaId = $this->resolveEstadoReservaId($servicio);

        return DB::transaction(function () use ($negocio, $servicio, $slot, $cliente, $contact, $policy, $pricing, $estadoReservaId, $input) {
            $resourceIds = $this->extractResourceIds($slot);
            $primaryResourceId = $resourceIds[0] ?? data_get($slot, 'recurso_id');

            if (! $primaryResourceId) {
                throw new RuntimeException('No se pudo resolver el recurso principal de la reserva.');
            }

            $reserva = Reserva::create([
                'negocio_id' => $negocio->id,
                'servicio_id' => $servicio->id,
                'recurso_id' => $primaryResourceId,
                'cliente_id' => $cliente->id,
                'nombre_responsable' => $contact['name'],
                'email_responsable' => $contact['email'],
                'telefono_responsable' => $contact['phone'],
                'tipo_documento_responsable' => $contact['document_type'],
                'documento_responsable' => $contact['document_value'],
                'fecha' => $input->fecha,
                'hora_inicio' => $this->normalizeStoredTime((string) data_get($slot, 'hora_inicio')),
                'hora_fin' => $this->normalizeStoredTime((string) data_get($slot, 'hora_fin')),
                'numero_personas' => $input->numero_personas,
                'precio_calculado' => $pricing['precio_calculado'],
                'precio_total' => null,
                'estado_reserva_id' => $estadoReservaId,
                'notas' => $contact['notes'],
                'localizador' => Reserva::generarLocalizador(),
                'documentacion_entregada' => false,
                'horas_minimas_cancelacion' => $policy['horas_minimas_cancelacion'],
                'permite_modificacion' => $policy['permite_modificacion'],
                'es_reembolsable' => $policy['es_reembolsable'],
                'porcentaje_senal' => $policy['porcentaje_senal'],
                'origen_reserva' => 'chat',
                'importada_externamente' => false,
            ]);

            foreach ($resourceIds as $resourceId) {
                ReservaRecurso::updateOrCreate(
                    [
                        'reserva_id' => $reserva->id,
                        'recurso_id' => $resourceId,
                    ],
                    [
                        'fecha' => $reserva->fecha,
                        'hora_inicio' => $reserva->hora_inicio,
                        'hora_fin' => $reserva->hora_fin,
                        'fecha_inicio_datetime' => $reserva->inicio_datetime,
                        'fecha_fin_datetime' => $reserva->fin_datetime,
                        'notas' => null,
                    ]
                );
            }

            return $reserva->fresh([
                'cliente',
                'servicio',
                'recurso',
                'estadoReserva',
                'reservaRecursos.recurso',
            ]);
        });
    }

    private function resolveSlot(CreateBookingInput $input, Servicio $servicio): array
    {
        $availabilityResult = $this->searchAvailabilityTool->execute([
            'negocio_id' => $input->negocio_id,
            'servicio_id' => $servicio->id,
            'fecha' => $input->fecha,
            'numero_personas' => $input->numero_personas,
        ]);

        if (! $availabilityResult->success) {
            throw new RuntimeException($availabilityResult->error ?? 'No se pudo comprobar la disponibilidad en este momento.');
        }

        $slots = data_get($availabilityResult->data, 'slots', []);
        if (! is_array($slots) || $slots === []) {
            throw new RuntimeException('No hay huecos disponibles para los datos indicados.');
        }

        $matches = collect($slots)->filter(function (array $slot) use ($input) {
            if ($input->slot_key !== null && ($slot['slot_key'] ?? null) !== $input->slot_key) {
                return false;
            }

            if ($input->hora_inicio !== null && ($slot['hora_inicio'] ?? null) !== $this->normalizeInputTime($input->hora_inicio)) {
                return false;
            }

            if ($input->hora_fin !== null && ($slot['hora_fin'] ?? null) !== $this->normalizeInputTime($input->hora_fin)) {
                return false;
            }

            if ($input->recurso_id !== null && (int) ($slot['recurso_id'] ?? 0) !== $input->recurso_id) {
                return false;
            }

            if ($input->recurso_ids !== []) {
                $slotResourceIds = $this->extractResourceIds($slot);
                sort($slotResourceIds);
                $requested = $input->recurso_ids;
                sort($requested);

                if ($slotResourceIds !== $requested) {
                    return false;
                }
            }

            return true;
        })->values();

        if ($matches->isEmpty()) {
            throw new RuntimeException('El hueco elegido ya no está disponible o no coincide con la disponibilidad actual.');
        }

        return $matches->first();
    }

    private function resolveCliente(array $contact): Cliente
    {
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

        if (! $cliente) {
            return Cliente::create([
                'nombre' => $contact['name'],
                'email' => $contact['email'],
                'telefono' => $contact['phone'],
                'notas' => null,
            ]);
        }

        $cliente->fill([
            'nombre' => $contact['name'],
            'email' => $contact['email'] ?? $cliente->email,
            'telefono' => $contact['phone'] ?? $cliente->telefono,
        ]);
        $cliente->save();

        return $cliente;
    }

    private function normalizeContactData(CreateBookingInput $input): array
    {
        $name = $this->collapseSpaces($input->contact_name);
        $phone = $this->collapseSpaces($input->contact_phone);
        $email = $this->normalizeEmail($input->contact_email);
        $documentType = $this->collapseSpaces($input->document_type);
        $documentValue = $this->collapseSpaces($input->document_value);
        $notes = $this->collapseSpaces($input->notes);

        if ($name === null) {
            throw new RuntimeException('Falta el nombre de la persona responsable de la reserva.');
        }

        if ($phone === null) {
            throw new RuntimeException('Falta el teléfono de contacto para cerrar la reserva.');
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
            throw new RuntimeException('Este servicio requiere documentación antes de cerrar la reserva. Necesito el tipo y el valor del documento.');
        }
    }

    private function resolvePricing(CreateBookingInput $input, Servicio $servicio, array $slot): array
    {
        $quote = $this->createQuoteTool->execute([
            'negocio_id' => $input->negocio_id,
            'servicio_id' => $servicio->id,
            'numero_personas' => $input->numero_personas,
            'inicio_datetime' => data_get($slot, 'inicio_datetime'),
            'fin_datetime' => data_get($slot, 'fin_datetime'),
        ]);

        if (! $quote->success) {
            throw new RuntimeException($quote->error ?? 'No se pudo calcular el precio de la reserva.');
        }

        return [
            'precio_calculado' => data_get($quote->data, 'precio_calculado', number_format((float) $servicio->precio_base, 2, '.', '')),
        ];
    }

    private function resolveEstadoReservaId(Servicio $servicio): int
    {
        $targetStatus = ($servicio->requiere_pago || filled($servicio->documentacion_requerida))
            ? 'Pendiente'
            : 'Confirmada';

        $id = EstadoReserva::query()
            ->where('nombre', $targetStatus)
            ->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $fallback = EstadoReserva::query()->orderBy('id')->value('id');

        if ($fallback === null) {
            throw new RuntimeException('No existe ningún estado de reserva configurado.');
        }

        return (int) $fallback;
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

        $resourceIds = data_get($slot, 'recurso_ids');
        if (is_array($resourceIds) && $resourceIds !== []) {
            return collect($resourceIds)
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $primary = data_get($slot, 'recurso_id');

        return is_numeric($primary) ? [(int) $primary] : [];
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
}
