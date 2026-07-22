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
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sub_title')->nullable();
            $table->string('slug');
            $table->foreignId('user_id');
            $table->foreignId('author_id')->nullable();
            $table->string('image_large')->nullable();
            $table->string('image_medium')->nullable();
            $table->string('image_small')->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('seo_title')->nullable();
            $table->longText('body');
            $table->text("short_description")->nullable();
            $table->unsignedInteger("lang_id")->index();
            $table->integer('visits')->default(0);
            $table->json('attachments')->nullable();
            $table->boolean('is_published')->default(0);
            $table->dateTime('publish_at')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcasts');
    }
};
