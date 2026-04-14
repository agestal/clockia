<?php

namespace Database\Seeders;

use App\Models\TipoBloqueo;
use Illuminate\Database\Seeder;

class TipoBloqueoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Descanso', 'descripcion' => 'Pausa operativa planificada del recurso.'],
            ['nombre' => 'Vacaciones', 'descripcion' => 'Periodo de cierre o ausencia prolongada.'],
            ['nombre' => 'Mantenimiento', 'descripcion' => 'Intervención técnica o revisión del recurso.'],
            ['nombre' => 'Evento especial', 'descripcion' => 'Uso extraordinario del recurso fuera de la operativa habitual.'],
            ['nombre' => 'Incidencia', 'descripcion' => 'Bloqueo por incidencia inesperada o avería puntual.'],
            ['nombre' => 'Cierre puntual', 'descripcion' => 'Cierre temporal por necesidad operativa concreta.'],
        ];

        foreach ($items as $item) {
            TipoBloqueo::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
