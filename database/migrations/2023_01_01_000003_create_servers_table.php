<?php

use App\Models\Credentials;
use App\Models\Team;
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
        Schema::create('servers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Credentials::class)->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('provider');
            $table->string('provider_id')->nullable();

            $table->string('region')->nullable();
            $table->string('type')->nullable();
            $table->string('image')->nullable();

            $table->integer('cpu_cores')->nullable();
            $table->integer('memory_in_mb')->nullable();
            $table->integer('storage_in_gb')->nullable();
            $table->string('operating_system')->nullable();

            $table->string('status');

            $table->string('public_ipv4')->nullable();

            $table->longText('public_key');
            $table->longText('private_key');
            $table->longText('user_public_key')->nullable();

            $table->string('username');
            $table->longText('password');
            $table->longText('database_password');
            $table->integer('ssh_port');
            $table->string('working_directory');
            $table->json('completed_provision_steps');
            $table->json('installed_software');
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('uninstallation_requested_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
