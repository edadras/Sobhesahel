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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id')->index();
            $table->text('body');
            $table->string('answerer_name')->nullable();
            $table->boolean('is_expert')->default(0); // پاسخ کارشناسی
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->boolean('is_published')->default(1);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
