<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->string('notif_email_destino')->nullable()->after('mail_encuesta_horas_despues');
            $table->boolean('notif_reserva_nueva')->default(false)->after('notif_email_destino');
            $table->boolean('notif_anulacion_reserva')->default(false)->after('notif_reserva_nueva');
            $table->boolean('notif_encuesta_respondida')->default(false)->after('notif_anulacion_reserva');
            $table->boolean('notif_aforo_lleno_experiencia')->default(false)->after('notif_encuesta_respondida');
            $table->boolean('notif_aforo_lleno_dia')->default(false)->after('notif_aforo_lleno_experiencia');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn([
                'notif_email_destino',
                'notif_reserva_nueva',
                'notif_anulacion_reserva',
                'notif_encuesta_respondida',
                'notif_aforo_lleno_experiencia',
                'notif_aforo_lleno_dia',
            ]);
        });
    }
};
