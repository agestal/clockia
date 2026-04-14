<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BaseCatalogsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TipoNegocioSeeder::class,
            TipoPrecioSeeder::class,
            TipoRecursoSeeder::class,
            TipoBloqueoSeeder::class,
            EstadoReservaSeeder::class,
            TipoPagoSeeder::class,
            EstadoPagoSeeder::class,
            ConceptoPagoSeeder::class,
        ]);
    }
}
