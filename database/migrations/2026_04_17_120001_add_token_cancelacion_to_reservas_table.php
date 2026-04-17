<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('token_cancelacion', 64)->nullable()->unique()->after('cancelada_por');
            $table->timestamp('token_cancelacion_expira_en')->nullable()->after('token_cancelacion');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['token_cancelacion', 'token_cancelacion_expira_en']);
        });
    }
};
