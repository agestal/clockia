<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recursos', function (Blueprint $table) {
            $table->integer('capacidad_minima')->nullable()->after('capacidad');
            $table->boolean('combinable')->default(false)->after('capacidad_minima');
            $table->text('notas_publicas')->nullable()->after('combinable');
        });
    }

    public function down(): void
    {
        Schema::table('recursos', function (Blueprint $table) {
            $table->dropColumn([
                'capacidad_minima',
                'combinable',
                'notas_publicas',
            ]);
        });
    }
};
