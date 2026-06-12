<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_tiers', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('product_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->after('customer_category_id')->constrained('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('price_tiers', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['category_id', 'customer_id']);
        });
    }
};
