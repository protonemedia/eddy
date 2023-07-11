<?php

use App\Models\Backup;
use App\Models\Disk;
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
        Schema::create('backup_jobs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('status');
            $table->foreignIdFor(Backup::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Disk::class)->constrained()->cascadeOnDelete();
            $table->integer('size')->nullable();
            $table->longText('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_jobs');
    }
};
