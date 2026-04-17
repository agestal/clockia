<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_onboarding_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('requested_tipo_negocio_id')
                ->nullable()
                ->constrained('tipos_negocio')
                ->nullOnDelete();
            $table->foreignId('provisioned_negocio_id')
                ->nullable()
                ->constrained('negocios')
                ->nullOnDelete();
            $table->string('status', 40)->default('pending')->index();
            $table->string('source_url', 500);
            $table->string('source_host', 255)->index();
            $table->string('requested_business_name')->nullable();
            $table->string('requested_admin_name')->nullable();
            $table->string('requested_admin_email')->nullable();
            $table->string('requested_admin_password_hash')->nullable();
            $table->json('draft_payload')->nullable();
            $table->json('missing_required_fields')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('discovery_started_at')->nullable();
            $table->timestamp('discovery_finished_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('business_onboarding_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_onboarding_session_id');
            $table->string('url', 500);
            $table->string('page_role', 80)->nullable();
            $table->string('title')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('content_type', 120)->nullable();
            $table->json('extracted_payload')->nullable();
            $table->timestamp('discovered_at')->nullable();
            $table->timestamps();

            $table->index('business_onboarding_session_id', 'bos_sources_session_idx');
            $table->foreign('business_onboarding_session_id', 'bos_sources_session_fk')
                ->references('id')
                ->on('business_onboarding_sessions')
                ->cascadeOnDelete();
            $table->unique(['business_onboarding_session_id', 'url'], 'bos_session_url_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_onboarding_sources');
        Schema::dropIfExists('business_onboarding_sessions');
    }
};
