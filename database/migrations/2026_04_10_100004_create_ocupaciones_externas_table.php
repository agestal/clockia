<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ocupaciones_externas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('integracion_id')->nullable()->constrained('integraciones');
            $table->foreignId('integracion_mapeo_id')->nullable()->constrained('integracion_mapeos');
            $table->foreignId('recurso_id')->nullable()->constrained('recursos');
            $table->string('proveedor')->nullable();
            $table->string('external_id');
            $table->string('external_calendar_id')->nullable();
            $table->string('titulo')->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->dateTime('inicio_datetime')->nullable();
            $table->dateTime('fin_datetime')->nullable();
            $table->boolean('es_dia_completo')->default(false);
            $table->string('origen')->nullable();
            $table->string('estado')->nullable();
            $table->json('payload_externo')->nullable();
            $table->dateTime('ultimo_sync_at')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'inicio_datetime', 'fin_datetime'], 'oe_negocio_datetime_idx');
            $table->index(['negocio_id', 'fecha'], 'oe_negocio_fecha_idx');
            $table->index(['recurso_id', 'inicio_datetime', 'fin_datetime'], 'oe_recurso_datetime_idx');
            $table->unique(['proveedor', 'external_id'], 'oe_proveedor_external_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ocupaciones_externas');
    }
};
