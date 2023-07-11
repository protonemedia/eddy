<?php

use App\Models\Disk;
use App\Models\Server;
use App\Models\User;
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
        Schema::create('backups', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Server::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Disk::class)->constrained();
            $table->string('name');
            $table->string('dispatch_token', 32);
            $table->string('cron_expression');
            $table->json('include_files');
            $table->json('exclude_files');
            $table->integer('retention')->default(14);
            $table->boolean('notification_on_failure')->default(true);
            $table->boolean('notification_on_success')->default(true);
            $table->string('notification_email')->nullable();
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
        Schema::dropIfExists('backups');
    }
};
