<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('name');
            $table->string('type', 30); // barred_price, period, bundle, free_product, buy_x_get_y, qty_discount, coupon
            $table->text('description')->nullable();
            $table->decimal('discount_value', 12, 2)->default(0); // amount or percentage
            $table->string('discount_type', 10)->default('percentage'); // percentage or fixed
            $table->decimal('min_purchase', 12, 2)->default(0);
            $table->integer('min_quantity')->default(0);
            $table->integer('max_quantity')->nullable();
            $table->integer('buy_quantity')->default(0); // for buy_x_get_y
            $table->integer('get_quantity')->default(0); // for buy_x_get_y
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('priority')->default(0);
            $table->json('conditions')->nullable(); // additional JSON conditions
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('promotion_product', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'product_id']);
        });

        Schema::create('promotion_category', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->primary(['promotion_id', 'category_id']);
        });

        Schema::create('promotion_customer', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'customer_id']);
        });

        Schema::create('promotion_store', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_store');
        Schema::dropIfExists('promotion_customer');
        Schema::dropIfExists('promotion_category');
        Schema::dropIfExists('promotion_product');
        Schema::dropIfExists('promotions');
    }
};
