<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * آرشیو خودکار — optional datetime on news; when it passes, app:cron
     * suspends the item and logs an "auto_archived" revision entry.
     */
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (! Schema::hasColumn('news', 'archive_at')) {
                $table->dateTime('archive_at')->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (Schema::hasColumn('news', 'archive_at')) {
                $table->dropColumn('archive_at');
            }
        });
    }
};
