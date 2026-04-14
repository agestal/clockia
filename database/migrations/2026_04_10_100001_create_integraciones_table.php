<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->string('proveedor');
            $table->string('nombre');
            $table->string('modo_operacion');
            $table->string('estado')->default('pendiente');
            $table->json('configuracion')->nullable();
            $table->dateTime('ultimo_sync_at')->nullable();
            $table->text('ultimo_error')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['negocio_id', 'proveedor']);
            $table->index(['negocio_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integraciones');
    }
};
