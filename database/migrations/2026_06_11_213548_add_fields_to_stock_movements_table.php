<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->foreignId('source_store_id')->nullable()->constrained('stores')->nullOnDelete()->after('store_id');
            $table->foreignId('destination_store_id')->nullable()->constrained('stores')->nullOnDelete()->after('source_store_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['unit', 'source_store_id', 'destination_store_id']);
        });
    }
};
