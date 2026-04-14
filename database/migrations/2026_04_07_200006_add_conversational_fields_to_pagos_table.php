<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('enlace_pago_externo', 1000)->nullable()->after('fecha_pago');
            $table->boolean('iniciado_por_bot')->default(false)->after('enlace_pago_externo');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'enlace_pago_externo',
                'iniciado_por_bot',
            ]);
        });
    }
};
