<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurso_combinaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurso_id')->constrained('recursos')->cascadeOnDelete();
            $table->foreignId('recurso_combinado_id')->constrained('recursos')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['recurso_id', 'recurso_combinado_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurso_combinaciones');
    }
};
