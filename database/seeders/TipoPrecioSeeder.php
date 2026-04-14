<?php

namespace Database\Seeders;

use App\Models\TipoPrecio;
use Illuminate\Database\Seeder;

class TipoPrecioSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Fijo', 'descripcion' => 'Precio cerrado independiente del número de asistentes.'],
            ['nombre' => 'Por persona', 'descripcion' => 'Precio calculado por cada persona incluida en la reserva.'],
            ['nombre' => 'Por tramo', 'descripcion' => 'Precio definido por franja horaria o duración del servicio.'],
            ['nombre' => 'Por servicio', 'descripcion' => 'Precio específico asociado al servicio contratado.'],
            ['nombre' => 'Personalizado', 'descripcion' => 'Precio ajustable manualmente según el caso.'],
        ];

        foreach ($items as $item) {
            TipoPrecio::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
