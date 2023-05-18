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
        Schema::create('crons', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Server::class)->constrained()->cascadeOnDelete();
            $table->string('user');
            $table->string('expression');
            $table->longText('command');
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
        Schema::dropIfExists('crons');
    }
};
