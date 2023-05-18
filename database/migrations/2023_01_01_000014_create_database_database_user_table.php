<?php

use App\Models\Database;
use App\Models\DatabaseUser;
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
        Schema::create('database_database_user', function (Blueprint $table) {
            $table->foreignIdFor(Database::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(DatabaseUser::class)->constrained()->cascadeOnDelete();
            $table->primary(['database_id', 'database_user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_database_user');
    }
};
