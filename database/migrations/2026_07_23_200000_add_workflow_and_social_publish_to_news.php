<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * گردش کار تحریریه + ارسال خودکار به شبکه‌های اجتماعی:
     * - Laravel's standard notifications table (used by Filament database
     *   notifications for the editorial approval workflow).
     * - Social auto-publish flags and one-shot "sent" markers on the news
     *   table (Telegram / WhatsApp), so a published item is never sent twice.
     *
     * Note: the `status` column is a plain string, so the new
     * `pending_review` status needs no schema change.
     */
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('news', function (Blueprint $table) {
            if (! Schema::hasColumn('news', 'auto_send_telegram')) {
                $table->boolean('auto_send_telegram')->default(false);
            }

            if (! Schema::hasColumn('news', 'auto_send_whatsapp')) {
                $table->boolean('auto_send_whatsapp')->default(false);
            }

            if (! Schema::hasColumn('news', 'telegram_sent_at')) {
                $table->timestamp('telegram_sent_at')->nullable();
            }

            if (! Schema::hasColumn('news', 'whatsapp_sent_at')) {
                $table->timestamp('whatsapp_sent_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            foreach ([
                'auto_send_telegram',
                'auto_send_whatsapp',
                'telegram_sent_at',
                'whatsapp_sent_at',
            ] as $column) {
                if (Schema::hasColumn('news', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('notifications');
    }
};
