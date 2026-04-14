<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dateTime('inicio_datetime')->nullable()->after('hora_fin');
            $table->dateTime('fin_datetime')->nullable()->after('inicio_datetime');

            $table->integer('horas_minimas_cancelacion')->nullable()->after('documentacion_entregada');
            $table->boolean('permite_modificacion')->nullable()->after('horas_minimas_cancelacion');
            $table->boolean('es_reembolsable')->nullable()->after('permite_modificacion');
            $table->decimal('porcentaje_senal', 5, 2)->nullable()->after('es_reembolsable');

            $table->index(['inicio_datetime', 'fin_datetime']);
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex(['inicio_datetime', 'fin_datetime']);
            $table->dropColumn([
                'inicio_datetime',
                'fin_datetime',
                'horas_minimas_cancelacion',
                'permite_modificacion',
                'es_reembolsable',
                'porcentaje_senal',
            ]);
        });
    }
};
