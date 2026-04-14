<?php

namespace Database\Seeders;

use App\Models\TipoRecurso;
use Illuminate\Database\Seeder;

class TipoRecursoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Mesa', 'descripcion' => 'Recurso de atención presencial con capacidad variable.'],
            ['nombre' => 'Sala', 'descripcion' => 'Espacio cerrado para grupos o eventos privados.'],
            ['nombre' => 'Cabina', 'descripcion' => 'Espacio individual o reducido para atención especializada.'],
            ['nombre' => 'Profesional', 'descripcion' => 'Recurso humano asignable a un servicio o cita.'],
            ['nombre' => 'Puesto', 'descripcion' => 'Puesto de trabajo o atención reservable.'],
            ['nombre' => 'Box', 'descripcion' => 'Zona técnica o compartimentada para uso operativo.'],
            ['nombre' => 'Vehículo', 'descripcion' => 'Unidad móvil o vehículo reservable dentro del negocio.'],
            ['nombre' => 'Habitación', 'descripcion' => 'Unidad de alojamiento o estancia individual.'],
        ];

        foreach ($items as $item) {
            TipoRecurso::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
