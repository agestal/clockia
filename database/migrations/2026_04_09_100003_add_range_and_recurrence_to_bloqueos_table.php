<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bloqueos', function (Blueprint $table) {
            $table->foreignId('negocio_id')->nullable()->after('id')->constrained('negocios');
            $table->date('fecha_inicio')->nullable()->after('fecha');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            $table->boolean('es_recurrente')->default(false)->after('fecha_fin');
            $table->tinyInteger('dia_semana')->nullable()->after('es_recurrente');
            $table->boolean('activo')->default(true)->after('motivo');

            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index(['es_recurrente', 'dia_semana']);
            $table->index(['negocio_id', 'activo']);
        });

        Schema::table('bloqueos', function (Blueprint $table) {
            $table->foreignId('recurso_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bloqueos', function (Blueprint $table) {
            $table->foreignId('recurso_id')->nullable(false)->change();
        });

        Schema::table('bloqueos', function (Blueprint $table) {
            $table->dropIndex(['fecha_inicio', 'fecha_fin']);
            $table->dropIndex(['es_recurrente', 'dia_semana']);
            $table->dropIndex(['negocio_id', 'activo']);
            $table->dropConstrainedForeignId('negocio_id');
            $table->dropColumn([
                'fecha_inicio',
                'fecha_fin',
                'es_recurrente',
                'dia_semana',
                'activo',
            ]);
        });
    }
};
