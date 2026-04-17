<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->json('dias_apertura')->nullable()->after('zona_horaria');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->unsignedInteger('aforo')->nullable()->after('numero_personas_maximo');
            $table->time('hora_inicio')->nullable()->after('aforo');
            $table->time('hora_fin')->nullable()->after('hora_inicio');

            $table->index(['negocio_id', 'activo', 'hora_inicio'], 'servicios_negocio_activo_hora_inicio_idx');
        });

        Schema::table('bloqueos', function (Blueprint $table) {
            $table->foreignId('servicio_id')
                ->nullable()
                ->after('negocio_id')
                ->constrained('servicios')
                ->nullOnDelete();

            $table->index(['servicio_id', 'activo'], 'bloqueos_servicio_activo_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bloqueos', function (Blueprint $table) {
            $table->dropIndex('bloqueos_servicio_activo_idx');
            $table->dropConstrainedForeignId('servicio_id');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->dropIndex('servicios_negocio_activo_hora_inicio_idx');
            $table->dropColumn([
                'aforo',
                'hora_inicio',
                'hora_fin',
            ]);
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn('dias_apertura');
        });
    }
};
