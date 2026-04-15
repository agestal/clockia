<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dateTime('mail_confirmacion_enviado_en')->nullable()->after('importada_externamente');
            $table->dateTime('mail_recordatorio_enviado_en')->nullable()->after('mail_confirmacion_enviado_en');
            $table->dateTime('mail_encuesta_enviado_en')->nullable()->after('mail_recordatorio_enviado_en');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'mail_confirmacion_enviado_en',
                'mail_recordatorio_enviado_en',
                'mail_encuesta_enviado_en',
            ]);
        });
    }
};
