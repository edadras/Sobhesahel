<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * زنجیره ورود روزانه اعضا (streak) — یک ردیف به‌ازای هر عضو.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('login_streaks')) {
            return;
        }

        Schema::create('login_streaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->unique();
            $table->unsignedInteger('current_days')->default(0);
            $table->unsignedInteger('longest_days')->default(0);
            $table->date('last_login_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_streaks');
    }
};
