<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('yektanet_ads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('code');
            $table->string('position');
            $table->json('page_scopes');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('lang_id')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('yektanet_ads');
    }
};
