<?php

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
        Schema::create('title_values', function (Blueprint $table) {
            $table->id();
            $table->morphs('titleable'); // Polymorphic relation (titleable_id, titleable_type)
            $table->string('title')->index(); // Title as a unique identifier
            $table->string('value')->nullable(); // Main value (can be string, number, etc.)
            $table->json('data')->nullable(); // JSON column for additional data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('title_values');
    }
};
