<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('reserved_stock', 15, 2)->default(0)->after('stock_quantity');
            $table->decimal('damaged_stock', 15, 2)->default(0)->after('reserved_stock');
            $table->decimal('blocked_stock', 15, 2)->default(0)->after('damaged_stock');
            $table->decimal('transit_stock', 15, 2)->default(0)->after('blocked_stock');
        });
        Schema::table('product_store', function (Blueprint $table) {
            $table->decimal('stock_quantity', 15, 2)->default(0)->after('store_id');
            $table->decimal('reserved_stock', 15, 2)->default(0)->after('stock_quantity');
            $table->decimal('damaged_stock', 15, 2)->default(0)->after('reserved_stock');
            $table->decimal('blocked_stock', 15, 2)->default(0)->after('damaged_stock');
        });
        // Backfill pivot stock from products table
        DB::statement('UPDATE product_store ps JOIN products p ON p.id = ps.product_id SET ps.stock_quantity = COALESCE(p.stock_quantity, 0)');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reserved_stock', 'damaged_stock', 'blocked_stock', 'transit_stock']);
        });
        Schema::table('product_store', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'reserved_stock', 'damaged_stock', 'blocked_stock']);
        });
    }
};
