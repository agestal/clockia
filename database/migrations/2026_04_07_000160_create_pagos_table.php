<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas');
            $table->foreignId('tipo_pago_id')->constrained('tipos_pago');
            $table->foreignId('estado_pago_id')->constrained('estados_pago');
            $table->decimal('importe', 10, 2);
            $table->string('referencia_externa')->nullable();
            $table->dateTime('fecha_pago')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
