<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disponibilidades', function (Blueprint $table) {
            $table->string('nombre_turno', 255)->nullable()->after('activo');
            $table->integer('buffer_minutos')->nullable()->default(null)->after('nombre_turno');
        });
    }

    public function down(): void
    {
        Schema::table('disponibilidades', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_turno',
                'buffer_minutos',
            ]);
        });
    }
};
