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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('user_id');
            $table->string('image_large')->nullable();
            $table->string('image_medium')->nullable();
            $table->string('image_small')->nullable();
            $table->text('meta_desc')->nullable();
            $table->string('seo_title')->nullable();
            $table->longText('body')->nullable();
            $table->text("short_description")->nullable();
            $table->unsignedInteger("lang_id")->index()->default(1);
            $table->integer('visits')->default(0);
            $table->integer('downloads')->default(0);
            $table->string('archive_file');
            $table->json('attachments')->nullable();
            $table->boolean('is_published')->default(0);
            $table->dateTime('publish_at')->nullable();
            $table->integer('archive_number');
            $table->date('archive_date')->nullable();
            $table->integer('category_id');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
