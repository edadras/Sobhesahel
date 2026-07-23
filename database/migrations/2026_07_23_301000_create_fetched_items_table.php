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
        Schema::create('fetched_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_source_id')->constrained('news_sources')->cascadeOnDelete();
            // Original feed identifier (guid / atom id / link), kept for reference.
            $table->string('guid', 2048)->nullable();
            // sha1(source id + guid-or-link) — dedupe key across runs.
            $table->string('guid_hash', 40)->unique();
            $table->string('title', 1024);
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('original_url', 2048)->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('status', 20)->default('new'); // new | saved | dismissed
            $table->foreignId('saved_news_id')->nullable()->constrained('news')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['news_source_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fetched_items');
    }
};
