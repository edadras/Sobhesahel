<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Roadmap section 8 (تبلیغات):
 *  - scheduling + limits columns on `advertises`
 *  - explicit ad `type` column (text|image|video) + video source fields
 *  - `advertise_events` raw click/impression event log
 *  - `advertise_daily_stats` daily aggregated views/clicks (flushed from cache
 *    by the `app:advertise-flush-stats` command every 10 minutes)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('advertises')) {
            Schema::table('advertises', function (Blueprint $table) {
                if (! Schema::hasColumn('advertises', 'type')) {
                    // text | image | video (nullable for legacy rows, backfilled below)
                    $table->string('type', 10)->nullable()->after('name');
                }

                if (! Schema::hasColumn('advertises', 'starts_at')) {
                    $table->dateTime('starts_at')->nullable()->after('is_active');
                }

                if (! Schema::hasColumn('advertises', 'ends_at')) {
                    $table->dateTime('ends_at')->nullable()->after('starts_at');
                }

                if (! Schema::hasColumn('advertises', 'max_clicks')) {
                    $table->unsignedInteger('max_clicks')->nullable()->after('ends_at');
                }

                if (! Schema::hasColumn('advertises', 'max_views')) {
                    $table->unsignedInteger('max_views')->nullable()->after('max_clicks');
                }

                if (! Schema::hasColumn('advertises', 'video')) {
                    // Uploaded video file path (storage disk).
                    $table->string('video')->nullable()->after('image');
                }

                if (! Schema::hasColumn('advertises', 'video_url')) {
                    // External (absolute) video URL, used instead of the uploaded file.
                    $table->string('video_url')->nullable()->after('video');
                }

                if (! Schema::hasColumn('advertises', 'video_duration')) {
                    // Video duration in seconds (used in the VAST document).
                    $table->unsignedSmallInteger('video_duration')->nullable()->after('video_url');
                }
            });

            // Backfill the type column for existing rows.
            DB::table('advertises')->whereNull('type')->whereNotNull('image')->update(['type' => 'image']);
            DB::table('advertises')->whereNull('type')->update(['type' => 'text']);
        }

        if (! Schema::hasTable('advertise_events')) {
            Schema::create('advertise_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('advertise_id')->index();
                $table->string('type', 10)->index(); // click | view
                $table->string('ip', 45)->nullable();
                $table->string('user_agent_hash', 64)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('advertise_daily_stats')) {
            Schema::create('advertise_daily_stats', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('advertise_id');
                $table->date('date');
                $table->unsignedInteger('views')->default(0);
                $table->unsignedInteger('clicks')->default(0);
                $table->timestamps();

                $table->unique(['advertise_id', 'date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertise_daily_stats');
        Schema::dropIfExists('advertise_events');

        if (Schema::hasTable('advertises')) {
            Schema::table('advertises', function (Blueprint $table) {
                foreach (['type', 'starts_at', 'ends_at', 'max_clicks', 'max_views', 'video', 'video_url', 'video_duration'] as $column) {
                    if (Schema::hasColumn('advertises', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
