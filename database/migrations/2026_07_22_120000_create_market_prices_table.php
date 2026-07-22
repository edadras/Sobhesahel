<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                   // e.g. دلار آمریکا
            $table->string('symbol')->unique();                       // e.g. usd, sekke_emami
            $table->string('category')->default('currency');          // currency|gold|coin|crypto|market
            $table->decimal('price', 20, 4)->default(0);
            $table->decimal('change_amount', 20, 4)->nullable();
            $table->decimal('change_percent', 8, 2)->nullable();
            $table->string('unit')->default('تومان');                 // e.g. تومان
            $table->string('source')->nullable();                     // provider name or "manual"
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'category', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_prices');
    }
};
