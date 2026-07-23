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
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // aparat | youtube | instagram | custom
            $table->string('platform')->default('aparat');
            // Only the stream/page URL is stored. Iframes are always built
            // server-side (LiveStream::getEmbedSrcAttribute) — never raw HTML.
            $table->string('embed_url', 2048);
            $table->text('description')->nullable();
            $table->boolean('is_live')->default(false);
            $table->dateTime('starts_at')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_streams');
    }
};
