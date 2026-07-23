<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * چرخ شانس باشگاه اعضا — جوایز (کاملاً قابل‌تعریف توسط مدیر؛ تصمیم ۵-۳)
 * و تاریخچه چرخش‌های هر عضو.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wheel_prizes')) {
            Schema::create('wheel_prizes', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->default('points'); // points|nothing|coupon_text
                $table->string('value')->nullable(); // مقدار امتیاز یا متن کد تخفیف
                $table->unsignedInteger('weight')->default(1); // وزن احتمال
                $table->unsignedInteger('stock')->nullable(); // null = نامحدود
                $table->boolean('is_active')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('wheel_spins')) {
            Schema::create('wheel_spins', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->index();
                $table->unsignedBigInteger('wheel_prize_id')->nullable()->index();
                $table->unsignedInteger('cost_points')->default(0); // 0 = چرخش رایگان روزانه
                $table->timestamp('created_at')->nullable();

                $table->index(['member_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wheel_spins');
        Schema::dropIfExists('wheel_prizes');
    }
};
