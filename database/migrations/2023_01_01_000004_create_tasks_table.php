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
        Schema::create('tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Server::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('user');
            $table->string('type');
            $table->longText('instance')->nullable();
            $table->longText('script');
            $table->integer('timeout');
            $table->string('status');
            $table->longText('output')->nullable();
            $table->integer('exit_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
