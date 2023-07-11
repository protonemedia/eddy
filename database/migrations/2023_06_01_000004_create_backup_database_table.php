<?php

use App\Models\Backup;
use App\Models\Database;
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
        Schema::create('backup_database', function (Blueprint $table) {
            $table->foreignIdFor(Backup::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Database::class)->constrained()->cascadeOnDelete();
            $table->unique(['backup_id', 'database_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_database');
    }
};
