<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ماموریت‌های باشگاه اعضا (روزانه/هفتگی/یک‌باره) و پیشرفت هر عضو در هر دوره.
 *
 * period_key نمونه‌ها: «2026-07-24» (روزانه)، «2026-W30» (هفتگی)، «once».
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('missions')) {
            Schema::create('missions', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('points')->default(0);
                $table->string('period')->default('daily'); // daily|weekly|once
                $table->unsignedInteger('goal_count')->default(1);
                $table->string('event_code')->index();
                $table->boolean('is_active')->default(true);
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mission_completions')) {
            Schema::create('mission_completions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->index();
                $table->unsignedBigInteger('mission_id')->index();
                $table->string('period_key');
                $table->unsignedInteger('progress')->default(0);
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->unique(['member_id', 'mission_id', 'period_key'], 'mc_member_mission_period_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_completions');
        Schema::dropIfExists('missions');
    }
};
