<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integracion_mapeos', function (Blueprint $table) {
            $table->string('timezone')->nullable()->after('nombre_externo');
            $table->boolean('es_primario')->default(false)->after('timezone');
            $table->boolean('seleccionado')->default(false)->after('es_primario');
            $table->json('datos_extra')->nullable()->after('configuracion');

            $table->index(['integracion_id', 'tipo_origen', 'seleccionado'], 'im_integracion_tipo_selected_idx');
        });

        Schema::table('reserva_integraciones', function (Blueprint $table) {
            $table->string('external_id')->nullable()->change();
            $table->index(['proveedor', 'external_calendar_id', 'external_id'], 'ri_provider_calendar_external_idx');
        });

        Schema::table('ocupaciones_externas', function (Blueprint $table) {
            $table->dropUnique('oe_proveedor_external_unique');
            $table->unique(['proveedor', 'external_calendar_id', 'external_id'], 'oe_provider_calendar_external_unique');
        });
    }

    public function down(): void
    {
        Schema::table('ocupaciones_externas', function (Blueprint $table) {
            $table->dropUnique('oe_provider_calendar_external_unique');
            $table->unique(['proveedor', 'external_id'], 'oe_proveedor_external_unique');
        });

        Schema::table('reserva_integraciones', function (Blueprint $table) {
            $table->dropIndex('ri_provider_calendar_external_idx');
            $table->string('external_id')->nullable(false)->change();
        });

        Schema::table('integracion_mapeos', function (Blueprint $table) {
            $table->dropIndex('im_integracion_tipo_selected_idx');
            $table->dropColumn([
                'timezone',
                'es_primario',
                'seleccionado',
                'datos_extra',
            ]);
        });
    }
};
