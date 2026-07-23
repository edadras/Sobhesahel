<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * دفتر کل (ledger) امتیاز اعضا — هر کسب یا خرج امتیاز یک ردیف تغییرناپذیر.
 *
 * member_id عمداً بدون foreign key است: جدول members در بسته موازی M1 ساخته
 * می‌شود و ترتیب اجرای migration ها نباید به هم وابسته باشد.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('point_transactions')) {
            return;
        }

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id')->index();
            $table->integer('points');
            $table->integer('balance_after')->default(0);
            $table->string('rule_code')->index();
            $table->string('description')->nullable();
            $table->nullableMorphs('reference');
            $table->timestamp('created_at')->nullable()->index();

            $table->index(['member_id', 'created_at']);
            $table->index(['member_id', 'rule_code', 'created_at'], 'pt_member_rule_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
