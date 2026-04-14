<?php

namespace Database\Seeders;

use App\Models\EstadoPago;
use Illuminate\Database\Seeder;

class EstadoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Pendiente', 'descripcion' => 'Pago todavía no confirmado o pendiente de cobro.'],
            ['nombre' => 'Pagado', 'descripcion' => 'Pago cobrado correctamente.'],
            ['nombre' => 'Reembolsado', 'descripcion' => 'Pago devuelto total o parcialmente al cliente.'],
            ['nombre' => 'Fallido', 'descripcion' => 'Intento de cobro que no pudo completarse.'],
            ['nombre' => 'Cancelado', 'descripcion' => 'Pago cancelado antes de su finalización.'],
        ];

        foreach ($items as $item) {
            EstadoPago::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
