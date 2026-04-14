<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('origen_reserva')->nullable()->after('porcentaje_senal');
            $table->boolean('importada_externamente')->default(false)->after('origen_reserva');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['origen_reserva', 'importada_externamente']);
        });
    }
};
