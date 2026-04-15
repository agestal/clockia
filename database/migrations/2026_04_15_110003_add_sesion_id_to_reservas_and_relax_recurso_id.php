<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->foreignId('sesion_id')
                ->nullable()
                ->after('servicio_id')
                ->constrained('sesiones')
                ->nullOnDelete();

            $table->foreignId('recurso_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sesion_id');
            $table->foreignId('recurso_id')->nullable(false)->change();
        });
    }
};
