<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->string('barcode')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('unit_sale')->default('piece');
            $table->string('unit_purchase')->default('piece');
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->default(0);
            $table->decimal('wholesale_price', 15, 2)->nullable();
            $table->decimal('promo_price', 15, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->nullable();
            $table->integer('alert_threshold')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sellable')->default(true);
            $table->boolean('is_stockable')->default(true);
            $table->boolean('track_lot')->default(false);
            $table->boolean('track_serial')->default(false);
            $table->boolean('track_expiry')->default(false);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('volume', 10, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('brand')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
