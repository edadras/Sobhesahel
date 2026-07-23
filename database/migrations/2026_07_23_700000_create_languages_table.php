<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * زبان‌های سایت (چندزبانه — WP-15 / roadmap section 19).
     *
     * No rows are seeded: when the table is empty, fa (rtl, default) and en
     * (ltr) are treated as built-in languages by App\Models\Language.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('native_name');
            $table->string('code', 10)->unique();
            $table->enum('direction', ['rtl', 'ltr'])->default('rtl');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
