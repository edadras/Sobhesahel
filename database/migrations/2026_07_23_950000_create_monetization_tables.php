<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * درآمدزایی — payments core + رپورتاژ آگهی + اشتراک ویژه/دیجیتال.
 */
return new class extends Migration
{
    public function up(): void
    {
        // پرداخت‌ها — one row per payment attempt, attached to any payable
        // (ReportageOrder, Subscription, ...) via a morph.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // Public token used in customer-facing payment URLs.
            $table->uuid('token')->unique();
            $table->morphs('payable');
            // Amount in تومان (integer — no decimals used in IRR/Toman).
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('pending')->index(); // pending | paid | failed | manual_review
            $table->string('driver', 30)->default('offline');
            // Gateway ref id / offline tracking code entered by the customer.
            $table->string('ref_code')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('payer_mobile', 20)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });

        // پلن‌های اشتراک ویژه
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('duration_days');
            // Price in تومان.
            $table->unsignedBigInteger('price');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        // سفارش‌های رپورتاژ آگهی
        Schema::create('reportage_orders', function (Blueprint $table) {
            $table->id();
            // Public token for the customer payment page (/reportage/pay/{token}).
            $table->uuid('token')->unique();
            $table->string('name');
            $table->string('mobile', 20);
            $table->string('email')->nullable();
            $table->string('subject');
            $table->text('brief');
            $table->string('link', 2048)->nullable();
            $table->date('desired_publish_date')->nullable();
            $table->string('status', 20)->default('new')->index(); // new | awaiting_payment | paid | published | rejected
            // Price in تومان, set by the admin before payment.
            $table->unsignedBigInteger('price')->nullable();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->foreignId('converted_news_id')->nullable()->constrained('news')->nullOnDelete();
            $table->text('reject_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // اشتراک‌های ویژه
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 20)->index();
            $table->string('email')->nullable();
            $table->foreignId('plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable()->index();
            // Code the subscriber uses to unlock digital publications (PDF archive).
            $table->string('access_code', 32)->unique();
            $table->string('status', 20)->default('pending')->index(); // pending | active | expired
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('reportage_orders');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('payments');
    }
};
