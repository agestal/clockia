<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->text('notas_publicas')->nullable()->after('activo');
            $table->text('instrucciones_previas')->nullable()->after('notas_publicas');
            $table->text('documentacion_requerida')->nullable()->after('instrucciones_previas');
            $table->integer('horas_minimas_cancelacion')->nullable()->after('documentacion_requerida');
            $table->boolean('es_reembolsable')->default(true)->after('horas_minimas_cancelacion');
            $table->decimal('porcentaje_senal', 5, 2)->nullable()->after('es_reembolsable');
            $table->boolean('precio_por_unidad_tiempo')->default(false)->after('porcentaje_senal');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn([
                'notas_publicas',
                'instrucciones_previas',
                'documentacion_requerida',
                'horas_minimas_cancelacion',
                'es_reembolsable',
                'porcentaje_senal',
                'precio_por_unidad_tiempo',
            ]);
        });
    }
};
