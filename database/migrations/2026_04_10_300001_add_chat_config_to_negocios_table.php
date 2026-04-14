<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->text('chat_personality')->nullable()->after('max_recursos_combinables');
            $table->json('chat_required_fields')->nullable()->after('chat_personality');
        });
    }

    public function down(): void
    {
        Schema::table('negocios', function (Blueprint $table) {
            $table->dropColumn(['chat_personality', 'chat_required_fields']);
        });
    }
};
