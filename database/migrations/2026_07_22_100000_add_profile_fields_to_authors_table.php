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
        Schema::table('authors', function (Blueprint $table) {
            $table->string('type')->nullable()->after('nik_name');
            $table->longText('resume')->nullable()->after('bio');
            $table->json('work_history')->nullable()->after('resume');
            $table->json('social_links')->nullable()->after('work_history');
            $table->string('email')->nullable()->after('social_links');
            $table->string('phone')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'resume',
                'work_history',
                'social_links',
                'email',
                'phone',
            ]);
        });
    }
};
