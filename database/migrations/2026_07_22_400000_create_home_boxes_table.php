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
        Schema::create('home_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('box_type')->default('category_row');
            $table->json('category_ids')->nullable();
            $table->unsignedInteger('items_count')->default(6);
            $table->unsignedInteger('lead_chars')->nullable();
            $table->string('content_type')->default('news');
            $table->unsignedInteger('time_range_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('home_boxes');
    }
};
