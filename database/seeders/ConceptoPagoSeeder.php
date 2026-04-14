<?php

namespace Database\Seeders;

use App\Models\ConceptoPago;
use Illuminate\Database\Seeder;

class ConceptoPagoSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nombre' => 'Señal', 'descripcion' => 'Importe parcial cobrado por adelantado para asegurar la reserva.'],
            ['nombre' => 'Pago final', 'descripcion' => 'Importe completo o restante abonado al finalizar el servicio.'],
            ['nombre' => 'Reembolso', 'descripcion' => 'Devolución total o parcial al cliente.'],
            ['nombre' => 'Penalización', 'descripcion' => 'Cargo aplicado por cancelación tardía o incumplimiento.'],
            ['nombre' => 'Garantía', 'descripcion' => 'Importe retenido como fianza o garantía, reembolsable si no hay incidencias.'],
        ];

        foreach ($items as $item) {
            ConceptoPago::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['descripcion' => $item['descripcion']]
            );
        }
    }
}
