<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('margin_rate', 5, 2)->nullable()->after('is_active');
            $table->decimal('min_margin', 5, 2)->nullable()->after('margin_rate');
            $table->integer('stock_threshold')->nullable()->after('min_margin');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['margin_rate', 'min_margin', 'stock_threshold']);
        });
    }
};
