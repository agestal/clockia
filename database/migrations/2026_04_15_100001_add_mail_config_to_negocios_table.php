<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->boolean('mail_confirmacion_activo')->default(false)->after('chat_system_rules');
            $table->boolean('mail_recordatorio_activo')->default(false)->after('mail_confirmacion_activo');
            $table->unsignedSmallInteger('mail_recordatorio_horas_antes')->default(24)->after('mail_recordatorio_activo');
            $table->boolean('mail_encuesta_activo')->default(false)->after('mail_recordatorio_horas_antes');
            $table->unsignedSmallInteger('mail_encuesta_horas_despues')->default(24)->after('mail_encuesta_activo');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn([
                'mail_confirmacion_activo',
                'mail_recordatorio_activo',
                'mail_recordatorio_horas_antes',
                'mail_encuesta_activo',
                'mail_encuesta_horas_despues',
            ]);
        });
    }
};
