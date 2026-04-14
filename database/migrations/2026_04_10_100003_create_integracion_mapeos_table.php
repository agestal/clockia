<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integracion_mapeos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integracion_id')->constrained('integraciones')->cascadeOnDelete();
            $table->string('tipo_origen');
            $table->string('external_id');
            $table->string('external_parent_id')->nullable();
            $table->string('nombre_externo')->nullable();
            $table->foreignId('negocio_id')->nullable()->constrained('negocios');
            $table->foreignId('recurso_id')->nullable()->constrained('recursos');
            $table->foreignId('servicio_id')->nullable()->constrained('servicios');
            $table->json('configuracion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['integracion_id', 'tipo_origen']);
            $table->unique(['integracion_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integracion_mapeos');
    }
};
