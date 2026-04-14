<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reserva_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('recurso_id')->constrained('recursos');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->dateTime('fecha_inicio_datetime')->nullable();
            $table->dateTime('fecha_fin_datetime')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['reserva_id', 'recurso_id']);
            $table->index(['recurso_id', 'fecha']);
            $table->index(['fecha_inicio_datetime', 'fecha_fin_datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_recursos');
    }
};
