<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            $table->decimal('margin_impact', 12, 2)->default(0)->after('refund_amount');
            $table->foreignId('credit_note_id')->nullable()->after('exchange_products')->constrained('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            $table->dropForeign(['credit_note_id']);
            $table->dropColumn(['margin_impact', 'credit_note_id']);
        });
    }
};
