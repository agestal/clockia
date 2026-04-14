<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integracion_cuentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integracion_id')->constrained('integraciones')->cascadeOnDelete();
            $table->string('cuenta_externa_id')->nullable();
            $table->string('email_externo')->nullable();
            $table->string('nombre_externo')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expira_en')->nullable();
            $table->text('scopes')->nullable();
            $table->json('datos_extra')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('integracion_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integracion_cuentas');
    }
};
