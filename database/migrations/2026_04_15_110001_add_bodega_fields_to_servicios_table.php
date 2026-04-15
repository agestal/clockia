<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->unsignedInteger('numero_personas_minimo')->nullable()->after('duracion_minutos');
            $table->unsignedInteger('numero_personas_maximo')->nullable()->after('numero_personas_minimo');
            $table->boolean('permite_menores')->default(false)->after('requiere_pago');
            $table->unsignedInteger('edad_minima')->nullable()->after('permite_menores');
            $table->decimal('precio_menor', 10, 2)->nullable()->after('precio_base');
            $table->json('idiomas')->nullable()->after('documentacion_requerida');
            $table->text('punto_encuentro')->nullable()->after('idiomas');
            $table->json('incluye')->nullable()->after('punto_encuentro');
            $table->json('no_incluye')->nullable()->after('incluye');
            $table->text('accesibilidad_notas')->nullable()->after('no_incluye');
            $table->boolean('requiere_aprobacion_manual')->default(false)->after('accesibilidad_notas');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn([
                'numero_personas_minimo',
                'numero_personas_maximo',
                'permite_menores',
                'edad_minima',
                'precio_menor',
                'idiomas',
                'punto_encuentro',
                'incluye',
                'no_incluye',
                'accesibilidad_notas',
                'requiere_aprobacion_manual',
            ]);
        });
    }
};
