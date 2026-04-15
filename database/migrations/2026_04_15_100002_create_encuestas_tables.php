<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Encuestas (one per reserva)
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->string('token', 64)->unique();
            $table->dateTime('enviada_en')->nullable();
            $table->dateTime('respondida_en')->nullable();
            $table->text('comentario_general')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'respondida_en']);
        });

        // Encuesta items (N evaluable items per survey)
        Schema::create('encuesta_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->string('clave', 100);
            $table->string('etiqueta');
            $table->text('descripcion')->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['negocio_id', 'clave']);
        });

        // Respuestas (one per item per encuesta)
        Schema::create('encuesta_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->cascadeOnDelete();
            $table->foreignId('encuesta_item_id')->constrained('encuesta_items');
            $table->unsignedTinyInteger('puntuacion'); // 0-10
            $table->timestamps();

            $table->unique(['encuesta_id', 'encuesta_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuesta_respuestas');
        Schema::dropIfExists('encuesta_items');
        Schema::dropIfExists('encuestas');
    }
};
