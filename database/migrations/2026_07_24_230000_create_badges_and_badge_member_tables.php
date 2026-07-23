<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نشان‌های باشگاه اعضا و جدول اتصال عضو↔نشان.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('badges')) {
            Schema::create('badges', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('icon')->nullable(); // ایموجی، متن کوتاه یا مسیر تصویر
                $table->string('condition_type')->default('manual'); // points_total|streak_days|missions_completed|transactions_count|manual
                $table->unsignedInteger('condition_value')->default(0);
                $table->boolean('is_active')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('badge_member')) {
            Schema::create('badge_member', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->index();
                $table->unsignedBigInteger('badge_id')->index();
                $table->timestamp('awarded_at')->nullable();

                $table->unique(['member_id', 'badge_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_member');
        Schema::dropIfExists('badges');
    }
};
