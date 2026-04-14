<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_integraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('integracion_id')->nullable()->constrained('integraciones');
            $table->string('proveedor');
            $table->string('external_id');
            $table->string('external_calendar_id')->nullable();
            $table->string('direccion_sync')->nullable();
            $table->dateTime('ultimo_sync_at')->nullable();
            $table->string('estado_sync')->nullable();
            $table->json('payload_resumen')->nullable();
            $table->timestamps();

            $table->unique(['reserva_id', 'proveedor']);
            $table->index(['proveedor', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_integraciones');
    }
};
