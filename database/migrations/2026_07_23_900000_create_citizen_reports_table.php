<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * شهروند خبرنگار — public citizen-journalist reports (roadmap section 21).
     */
    public function up(): void
    {
        Schema::create('citizen_reports', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('mobile', 20);
            $table->string('email', 190)->nullable();
            $table->string('title');
            $table->text('body');
            // [{path, type: image|video, name, size}, ...] stored on the public disk under citizen-reports/
            $table->json('attachments')->nullable();
            $table->string('location')->nullable();
            $table->string('status', 20)->default('new')->index(); // new|reviewing|accepted|rejected
            $table->foreignId('reviewer_id')->nullable();
            $table->text('review_note')->nullable();
            $table->unsignedBigInteger('converted_news_id')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizen_reports');
    }
};
