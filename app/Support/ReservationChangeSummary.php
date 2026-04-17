<?php

namespace App\Support;

use App\Models\Reserva;

class ReservationChangeSummary
{
    public static function build(Reserva $before, Reserva $after): array
    {
        $before->loadMissing('servicio:id,nombre');
        $after->loadMissing('servicio:id,nombre');

        $changes = [];

        foreach (self::fieldMap() as $field => $config) {
            $beforeValue = ($config['resolver'])($before);
            $afterValue = ($config['resolver'])($after);

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => $config['label'],
                'before' => $beforeValue ?? 'Sin indicar',
                'after' => $afterValue ?? 'Sin indicar',
            ];
        }

        return $changes;
    }

    private static function fieldMap(): array
    {
        return [
            'service_name' => [
                'label' => 'Experiencia',
                'resolver' => fn (Reserva $reserva) => $reserva->servicio?->nombre,
            ],
            'date' => [
                'label' => 'Fecha',
                'resolver' => fn (Reserva $reserva) => $reserva->fecha?->format('d/m/Y'),
            ],
            'start_time' => [
                'label' => 'Hora de inicio',
                'resolver' => fn (Reserva $reserva) => filled($reserva->hora_inicio) ? substr((string) $reserva->hora_inicio, 0, 5) : null,
            ],
            'end_time' => [
                'label' => 'Hora de fin',
                'resolver' => fn (Reserva $reserva) => filled($reserva->hora_fin) ? substr((string) $reserva->hora_fin, 0, 5) : null,
            ],
            'party_size' => [
                'label' => 'Personas',
                'resolver' => fn (Reserva $reserva) => $reserva->numero_personas !== null ? (string) $reserva->numero_personas : null,
            ],
            'contact_name' => [
                'label' => 'Responsable',
                'resolver' => fn (Reserva $reserva) => self::collapse($reserva->nombreResponsableEfectivo()),
            ],
            'contact_phone' => [
                'label' => 'Telefono',
                'resolver' => fn (Reserva $reserva) => self::collapse($reserva->telefonoResponsableEfectivo()),
            ],
            'contact_email' => [
                'label' => 'Email',
                'resolver' => fn (Reserva $reserva) => self::collapse($reserva->emailResponsableEfectivo()),
            ],
            'notes' => [
                'label' => 'Notas',
                'resolver' => fn (Reserva $reserva) => self::collapse($reserva->notas),
            ],
        ];
    }

    private static function collapse(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/u', ' ', trim($value));

        return $value !== '' ? $value : null;
    }
}
