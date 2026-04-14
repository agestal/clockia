<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('recurso_id')->constrained('recursos');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('numero_personas')->nullable();
            $table->decimal('precio_calculado', 10, 2);
            $table->decimal('precio_total', 10, 2)->nullable();
            $table->foreignId('estado_reserva_id')->constrained('estados_reserva');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
