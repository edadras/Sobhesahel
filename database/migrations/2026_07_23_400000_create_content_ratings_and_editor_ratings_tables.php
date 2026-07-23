<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * امتیازدهی (roadmap section 3):
     * - content_ratings: public 1-5 star ratings on content (unique per ip per item).
     * - editor_ratings: editor 1-10 scores per news item, used in payroll reports
     *   (حقوق و دستمزد - roadmap section 16).
     */
    public function up(): void
    {
        if (! Schema::hasTable('content_ratings')) {
            Schema::create('content_ratings', function (Blueprint $table) {
                $table->id();
                $table->string('rateable_type');
                $table->unsignedBigInteger('rateable_id');
                $table->unsignedTinyInteger('rating'); // 1..5
                $table->string('ip', 45);
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('created_at')->nullable();

                $table->index(['rateable_type', 'rateable_id']);
                $table->unique(['rateable_type', 'rateable_id', 'ip'], 'content_ratings_ip_unique');
            });
        }

        if (! Schema::hasTable('editor_ratings')) {
            Schema::create('editor_ratings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // the editor
                $table->unsignedTinyInteger('score'); // 1..10
                $table->string('note', 500)->nullable();
                $table->timestamp('created_at')->nullable();

                $table->unique(['news_id', 'user_id'], 'editor_ratings_news_editor_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editor_ratings');
        Schema::dropIfExists('content_ratings');
    }
};
