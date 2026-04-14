<?php

namespace Database\Seeders;

use App\Models\TipoPago;
use Illuminate\Database\Seeder;

class TipoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Efectivo', 'descripcion' => 'Pago realizado en efectivo en el propio establecimiento.'],
            ['nombre' => 'Tarjeta', 'descripcion' => 'Pago realizado con tarjeta bancaria.'],
            ['nombre' => 'Bizum', 'descripcion' => 'Pago recibido mediante Bizum u otro medio instantáneo equivalente.'],
            ['nombre' => 'Transferencia', 'descripcion' => 'Pago recibido por transferencia bancaria.'],
            ['nombre' => 'TPV online', 'descripcion' => 'Pago procesado por pasarela o TPV online.'],
        ];

        foreach ($items as $item) {
            TipoPago::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
