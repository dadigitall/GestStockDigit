<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('family')->nullable()->after('brand');
            $table->string('packaging')->nullable()->after('unit_purchase');
            $table->decimal('reseller_price', 15, 2)->nullable()->after('wholesale_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['family', 'packaging', 'reseller_price']);
        });
    }
};
