<?php

use App\Models\Site;
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
        Schema::create('certificates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(Site::class)->constrained()->cascadeOnDelete();
            $table->longText('csr')->nullable();
            $table->longText('public_key')->nullable();
            $table->longText('private_key')->nullable();
            $table->longText('certificate')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
