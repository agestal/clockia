<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dateTime('mail_modificacion_enviado_en')->nullable()->after('mail_confirmacion_enviado_en');
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->boolean('notif_reserva_modificada')->default(true)->after('notif_reserva_nueva');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn('mail_modificacion_enviado_en');
        });

        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn('notif_reserva_modificada');
        });
    }
};
