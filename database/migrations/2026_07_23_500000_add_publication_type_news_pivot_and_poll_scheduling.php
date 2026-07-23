<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - archives: publication `type` (daily/special/quarterly/dossier) + manual `sort_order`
     * - archive_news: pivot connecting news items to a publication issue
     * - polls: scheduling window (starts_at/ends_at, guarded) + `poll_category`
     * - poll_votes: `user_ip` (guarded) for per-IP anti-fraud
     */
    public function up(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            if (! Schema::hasColumn('archives', 'type')) {
                $table->string('type')->default('daily')->index()->after('archive_number');
            }

            if (! Schema::hasColumn('archives', 'sort_order')) {
                $table->integer('sort_order')->default(0)->index()->after('archive_number');
            }
        });

        if (! Schema::hasTable('archive_news')) {
            Schema::create('archive_news', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('archive_id')->index();
                $table->unsignedBigInteger('news_id')->index();
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['archive_id', 'news_id']);
            });
        }

        Schema::table('polls', function (Blueprint $table) {
            if (! Schema::hasColumn('polls', 'starts_at')) {
                $table->timestamp('starts_at')->nullable();
            }

            if (! Schema::hasColumn('polls', 'ends_at')) {
                $table->timestamp('ends_at')->nullable();
            }

            if (! Schema::hasColumn('polls', 'poll_category')) {
                $table->string('poll_category')->nullable()->index();
            }
        });

        Schema::table('poll_votes', function (Blueprint $table) {
            if (! Schema::hasColumn('poll_votes', 'user_ip')) {
                $table->string('user_ip')->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('archives', function (Blueprint $table) {
            if (Schema::hasColumn('archives', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('archives', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::dropIfExists('archive_news');

        Schema::table('polls', function (Blueprint $table) {
            if (Schema::hasColumn('polls', 'poll_category')) {
                $table->dropColumn('poll_category');
            }
        });
    }
};
