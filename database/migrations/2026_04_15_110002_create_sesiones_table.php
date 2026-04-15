<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('recurso_id')->nullable()->constrained('recursos')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->dateTime('inicio_datetime')->nullable();
            $table->dateTime('fin_datetime')->nullable();
            $table->unsignedInteger('aforo_total')->nullable();
            $table->boolean('activo')->default(true);
            $table->text('notas_publicas')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'fecha', 'activo'], 'sesiones_negocio_fecha_activo_idx');
            $table->index(['servicio_id', 'fecha'], 'sesiones_servicio_fecha_idx');
            $table->index(['recurso_id', 'fecha'], 'sesiones_recurso_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones');
    }
};
