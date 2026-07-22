<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('ip', 45)->nullable()->index()->after('status');
            $table->string('user_agent', 512)->nullable()->after('ip');
            $table->string('spam_reason')->nullable()->after('user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['ip']);
            $table->dropColumn(['ip', 'user_agent', 'spam_reason']);
        });
    }
};
