<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('meta_desc')->nullable();
            $table->boolean('no_index')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['image', 'description', 'seo_title', 'meta_desc', 'no_index']);
        });
    }
};
