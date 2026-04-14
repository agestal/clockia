<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('localizador', 50)->nullable()->unique()->after('notas');
            $table->datetime('fecha_cancelacion')->nullable()->after('localizador');
            $table->text('motivo_cancelacion')->nullable()->after('fecha_cancelacion');
            $table->string('cancelada_por', 50)->nullable()->after('motivo_cancelacion');
            $table->text('instrucciones_llegada')->nullable()->after('cancelada_por');
            $table->datetime('fecha_estimada_fin')->nullable()->after('instrucciones_llegada');
            $table->boolean('documentacion_entregada')->default(false)->after('fecha_estimada_fin');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn([
                'localizador',
                'fecha_cancelacion',
                'motivo_cancelacion',
                'cancelada_por',
                'instrucciones_llegada',
                'fecha_estimada_fin',
                'documentacion_entregada',
            ]);
        });
    }
};
