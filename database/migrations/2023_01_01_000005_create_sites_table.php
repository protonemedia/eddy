<?php

use App\Models\Server;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Server::class)->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('type')->index();
            $table->string('tls_setting')->index();
            $table->timestamp('pending_tls_update_since')->nullable();
            $table->boolean('zero_downtime_deployment')->default(true);
            $table->integer('deployment_releases_retention')->default(10);
            $table->string('repository_url')->nullable();
            $table->string('repository_branch')->nullable();
            $table->string('deploy_token', 32);
            $table->string('deploy_notification_email')->nullable();
            $table->longText('deploy_key_public')->nullable();
            $table->longText('deploy_key_private')->nullable();
            $table->string('user');
            $table->string('path');
            $table->string('web_folder');
            $table->string('php_version')->nullable()->index();
            $table->timestamp('pending_caddyfile_update_since')->nullable();
            $table->json('shared_directories');
            $table->json('writeable_directories');
            $table->json('shared_files');
            $table->longText('hook_before_updating_repository')->nullable();
            $table->longText('hook_after_updating_repository')->nullable();
            $table->longText('hook_before_making_current')->nullable();
            $table->longText('hook_after_making_current')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('installation_failed_at')->nullable();
            $table->timestamp('uninstallation_requested_at')->nullable();
            $table->timestamp('uninstallation_failed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
