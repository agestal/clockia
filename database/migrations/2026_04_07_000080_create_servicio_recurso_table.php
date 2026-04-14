<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_recurso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('recurso_id')->constrained('recursos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_recurso');
    }
};
