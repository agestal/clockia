<?php

namespace App\Support;

use App\Models\Encuesta;
use App\Models\PlantillaEmail;
use App\Models\Reserva;

class EmailTemplateRenderer
{
    public function forConfirmation(Reserva $reserva): array
    {
        return $this->resolve($reserva, PlantillaEmail::TIPO_CONFIRMACION);
    }

    public function forReminder(Reserva $reserva): array
    {
        return $this->resolve($reserva, PlantillaEmail::TIPO_RECORDATORIO);
    }

    public function forSurvey(Reserva $reserva, Encuesta $encuesta): array
    {
        return $this->resolve($reserva, PlantillaEmail::TIPO_ENCUESTA, [
            'encuesta_url' => route('encuesta.show', $encuesta->token),
        ]);
    }

    private function resolve(Reserva $reserva, string $tipo, array $extraVariables = []): array
    {
        $negocio = $reserva->negocio;

        if (! $negocio) {
            return array_merge(
                PlantillaEmail::defaultsFor($tipo),
                ['encuesta_url' => $extraVariables['encuesta_url'] ?? null]
            );
        }

        PlantillaEmail::ensureDefaultsForBusiness($negocio);

        $template = PlantillaEmail::query()
            ->where('negocio_id', $negocio->id)
            ->where('tipo', $tipo)
            ->firstOrFail();

        $nombre = trim((string) ($reserva->nombreResponsableEfectivo() ?? ''));
        $variables = array_merge([
            'negocio' => $negocio->nombre ?? 'Clockia',
            'servicio' => $reserva->servicio?->nombre ?? 'tu experiencia',
            'fecha' => optional($reserva->fecha)->locale('es')->translatedFormat('l j \\d\\e F \\d\\e Y') ?? '',
            'hora' => substr((string) $reserva->hora_inicio, 0, 5),
            'personas' => $reserva->numero_personas !== null ? (string) $reserva->numero_personas : '',
            'localizador' => $reserva->localizador ?? '',
            'direccion' => $negocio->direccion ?? '',
            'telefono' => $negocio->telefono ?? '',
            'nombre' => $nombre,
            'nombre_fragmento' => $nombre !== '' ? ', '.$nombre : '',
        ], $extraVariables);

        $resolved = $template->resolved($variables);
        $resolved['encuesta_url'] = $extraVariables['encuesta_url'] ?? null;

        return $resolved;
    }
}
