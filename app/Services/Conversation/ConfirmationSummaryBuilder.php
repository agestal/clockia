<?php

namespace App\Services\Conversation;

use App\Models\Servicio;
use Carbon\Carbon;

class ConfirmationSummaryBuilder
{
    /**
     * Build from ConversationState (preferred — full context).
     */
    public function buildFromState(ConversationState $state): ConfirmationSummary
    {
        $lines = [];

        if ($state->servicioNombre) {
            $lines[] = "Servicio: {$state->servicioNombre}";
        }

        if ($state->fecha) {
            $lines[] = "Fecha: {$this->humanizeDate($state->fecha)}";
        }

        if ($state->numeroPersonas) {
            $lines[] = "Personas: {$state->numeroPersonas}";
        }

        if ($state->horaPreferida) {
            $lines[] = "Hora: {$state->horaPreferida}";
        }
        if ($state->contactName) {
            $lines[] = "Responsable: {$state->contactName}";
        }
        if ($state->contactPhone) {
            $lines[] = "Teléfono: {$state->contactPhone}";
        }

        if ($state->ultimaPropuesta !== null) {
            $hora = $state->ultimaPropuesta['hora_inicio'] ?? null;
            if ($hora && ! $state->horaPreferida) {
                $lines[] = "Hora: {$hora}";
            }
            $zone = $state->ultimaPropuesta['zone_label'] ?? null;
            if ($zone) {
                $lines[] = "Zona: {$zone}";
            }
        }

        $summary = "Perfecto 😊 Entonces sería:\n\n";
        foreach ($lines as $line) {
            $summary .= "• {$line}\n";
        }
        $summary .= "\n¿Te lo confirmo?";

        return new ConfirmationSummary(
            tool: 'create_booking',
            params: $state->buildToolParams(),
            summaryText: $summary,
            dataPoints: $lines,
        );
    }

    /**
     * Build from raw params (legacy compatibility).
     */
    public function build(string $tool, array $params, ?array $extraContext = null): ConfirmationSummary
    {
        $lines = [];

        if ($tool === 'modify_booking' && isset($params['locator'])) {
            $lines[] = "Localizador: {$params['locator']}";
        }

        if (isset($params['servicio_id'])) {
            $servicio = Servicio::find($params['servicio_id']);
            if ($servicio) {
                $lines[] = "Servicio: {$servicio->nombre}";
            }
        }

        if (isset($params['fecha'])) {
            $lines[] = "Fecha: {$this->humanizeDate($params['fecha'])}";
        }

        if (isset($params['numero_personas'])) {
            $lines[] = "Personas: {$params['numero_personas']}";
        }

        if (isset($params['hora_inicio'])) {
            $lines[] = "Hora: {$params['hora_inicio']}";
        }
        if (isset($params['contact_name'])) {
            $lines[] = "Responsable: {$params['contact_name']}";
        }
        if (isset($params['contact_phone'])) {
            $lines[] = "Teléfono: {$params['contact_phone']}";
        }

        $summary = $tool === 'modify_booking'
            ? "Perfecto. Voy a modificar la reserva con estos datos:\n\n"
            : "Perfecto 😊 Entonces sería:\n\n";
        foreach ($lines as $line) {
            $summary .= "• {$line}\n";
        }
        $summary .= "\n¿Te lo confirmo?";

        return new ConfirmationSummary(tool: $tool, params: $params, summaryText: $summary, dataPoints: $lines);
    }

    private function humanizeDate(string $date): string
    {
        try {
            $carbon = Carbon::parse($date)->locale('es');
            $today = Carbon::today();

            if ($carbon->isSameDay($today)) {
                return 'hoy ('.$carbon->translatedFormat('l j \d\e F').')';
            }
            if ($carbon->isSameDay($today->copy()->addDay())) {
                return 'mañana ('.$carbon->translatedFormat('l j \d\e F').')';
            }

            return $carbon->translatedFormat('l j \d\e F');
        } catch (\Throwable) {
            return $date;
        }
    }
}
