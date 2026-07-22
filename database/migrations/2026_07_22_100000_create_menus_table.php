<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->string('type')->default('link'); // link | content | separator
            $table->string('url')->nullable();
            $table->string('target')->default('_self'); // _self | _blank
            $table->string('image')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('location')->default('header'); // header | footer | mobile
            $table->timestamps();

            $table->index(['location', 'is_active']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
