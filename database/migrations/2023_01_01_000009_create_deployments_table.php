<?php

use App\Models\Site;
use App\Models\Task;
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
        Schema::create('deployments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Site::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Task::class)->nullable()->constrained()->nullOnDelete();
            $table->string('status');
            $table->string('git_hash')->nullable();
            $table->timestamp('user_notified_at')->nullable();
            $table->timestamps();
            $table->unique(['site_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
