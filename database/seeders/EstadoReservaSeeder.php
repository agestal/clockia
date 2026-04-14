<?php

namespace Database\Seeders;

use App\Models\EstadoReserva;
use Illuminate\Database\Seeder;

class EstadoReservaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Pendiente', 'descripcion' => 'Reserva registrada pendiente de confirmación final.'],
            ['nombre' => 'Confirmada', 'descripcion' => 'Reserva validada y prevista para atenderse.'],
            ['nombre' => 'Cancelada', 'descripcion' => 'Reserva anulada antes de su prestación.'],
            ['nombre' => 'Completada', 'descripcion' => 'Reserva ya atendida y cerrada correctamente.'],
            ['nombre' => 'No presentada', 'descripcion' => 'Reserva no atendida por ausencia del cliente.'],
        ];

        foreach ($items as $item) {
            EstadoReserva::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
