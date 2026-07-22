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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->integer('news_id')->nullable();
            $table->integer('note_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('reply_to')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->text('comment');
            $table->string('status')->default('pending');
            $table->unsignedSmallInteger('lang_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
