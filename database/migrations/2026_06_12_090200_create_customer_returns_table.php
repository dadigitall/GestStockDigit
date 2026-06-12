<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference', 50);
            $table->string('return_type', 20)->default('partial'); // total, partial, exchange
            $table->string('reason', 50); // defective, wrong_product, changed_mind, expired, damaged, other
            $table->text('reason_description')->nullable();
            $table->boolean('restock')->default(true);
            $table->string('refund_method', 30)->nullable(); // cash, mobile_money, card, credit_note, exchange
            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->decimal('exchange_amount', 12, 2)->default(0);
            $table->text('exchange_products')->nullable(); // JSON for exchanged products
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, completed
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_returns');
    }
};
