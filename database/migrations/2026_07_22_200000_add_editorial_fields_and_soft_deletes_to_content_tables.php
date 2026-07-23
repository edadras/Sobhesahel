<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - Soft deletes for the news cartables (کارتابل حذف‌شده) on all main content tables.
     * - Editorial form completion fields on the news table (بخش ۳ نقشه راه):
     *   unlimited subtitles, complementary images, main media file, pre/post
     *   messages, title color and display toggles.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (! Schema::hasColumn('news', 'deleted_at')) {
                $table->softDeletes();
            }

            if (! Schema::hasColumn('news', 'subtitles')) {
                $table->json('subtitles')->nullable();
            }

            if (! Schema::hasColumn('news', 'image_second')) {
                $table->string('image_second')->nullable();
            }

            if (! Schema::hasColumn('news', 'image_third')) {
                $table->string('image_third')->nullable();
            }

            if (! Schema::hasColumn('news', 'media_file')) {
                $table->string('media_file')->nullable();
            }

            if (! Schema::hasColumn('news', 'pre_message')) {
                $table->text('pre_message')->nullable();
            }

            if (! Schema::hasColumn('news', 'post_message')) {
                $table->text('post_message')->nullable();
            }

            if (! Schema::hasColumn('news', 'title_color')) {
                $table->string('title_color', 32)->nullable();
            }

            if (! Schema::hasColumn('news', 'show_visits')) {
                $table->boolean('show_visits')->default(true);
            }

            if (! Schema::hasColumn('news', 'show_comments')) {
                $table->boolean('show_comments')->default(true);
            }
        });

        foreach (['notes', 'videos', 'podcasts', 'galleries'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            foreach ([
                'deleted_at',
                'subtitles',
                'image_second',
                'image_third',
                'media_file',
                'pre_message',
                'post_message',
                'title_color',
                'show_visits',
                'show_comments',
            ] as $column) {
                if (Schema::hasColumn('news', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        foreach (['notes', 'videos', 'podcasts', 'galleries'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
