<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->text('descripcion_publica')->nullable()->after('activo');
            $table->string('direccion', 500)->nullable()->after('descripcion_publica');
            $table->string('url_publica', 500)->nullable()->after('direccion');
            $table->text('politica_cancelacion')->nullable()->after('url_publica');
            $table->integer('horas_minimas_cancelacion')->nullable()->after('politica_cancelacion');
            $table->boolean('permite_modificacion')->default(true)->after('horas_minimas_cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn([
                'descripcion_publica',
                'direccion',
                'url_publica',
                'politica_cancelacion',
                'horas_minimas_cancelacion',
                'permite_modificacion',
            ]);
        });
    }
};
