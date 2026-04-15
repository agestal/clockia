<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->boolean('widget_enabled')->default(false)->after('mail_encuesta_horas_despues');
            $table->string('widget_public_key', 64)->nullable()->unique()->after('widget_enabled');
            $table->json('widget_settings')->nullable()->after('widget_public_key');
        });

        \App\Models\Negocio::query()->whereNull('widget_public_key')->get()->each(function ($negocio) {
            $negocio->widget_public_key = (string) Str::uuid();
            $negocio->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropUnique(['widget_public_key']);
            $table->dropColumn(['widget_enabled', 'widget_public_key', 'widget_settings']);
        });
    }
};
