<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قوانین امتیاز باشگاه اعضا (تصمیم نهایی ۵-۳: نرخ‌ها کاملاً قابل‌تعریف توسط مدیر).
 *
 * هر رویداد امتیازآور یک ردیف دارد (کد یکتا مانند daily_login یا comment_approved)
 * و مدیر می‌تواند مقدار امتیاز، سقف روزانه و فعال/غیرفعال بودن را تغییر دهد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('point_rules')) {
            return;
        }

        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->integer('points')->default(0);
            $table->unsignedInteger('daily_cap')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_rules');
    }
};
