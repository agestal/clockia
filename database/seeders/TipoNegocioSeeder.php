<?php

namespace Database\Seeders;

use App\Models\TipoNegocio;
use Illuminate\Database\Seeder;

class TipoNegocioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Restaurante', 'descripcion' => 'Negocio de restauración con servicio en mesa o por turnos.'],
            ['nombre' => 'Clínica', 'descripcion' => 'Centro sanitario con agenda de pacientes y recursos.'],
            ['nombre' => 'Peluquería', 'descripcion' => 'Negocio de citas para servicios de peluquería y estética capilar.'],
            ['nombre' => 'Centro de estética', 'descripcion' => 'Espacio para tratamientos de belleza y bienestar.'],
            ['nombre' => 'Taller', 'descripcion' => 'Negocio con reservas de box, vehículos o intervenciones técnicas.'],
            ['nombre' => 'Hotel', 'descripcion' => 'Establecimiento de alojamiento con habitaciones y servicios reservables.'],
            ['nombre' => 'Coworking', 'descripcion' => 'Espacio compartido con puestos, salas y recursos reservables.'],
            ['nombre' => 'Gimnasio', 'descripcion' => 'Centro deportivo con clases, salas y recursos asociados.'],
        ];

        foreach ($items as $item) {
            TipoNegocio::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
