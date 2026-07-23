<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Audit trail for the news workflow (گردش خبر و تاریخچه تغییرات).
     * Body-sized fields are stored only as hash/length inside changed_fields.
     */
    public function up(): void
    {
        Schema::create('news_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32)->index(); // created|updated|status_changed|deleted|restored
            $table->json('changed_fields')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['news_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_revisions');
    }
};
