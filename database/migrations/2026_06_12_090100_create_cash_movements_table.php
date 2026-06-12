<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->morphs('sourceable'); // sale, invoice, customer_return, etc.
            $table->string('type', 30); // cash_sale, customer_payment, customer_refund, supplier_payment, internal_expense, owner_withdrawal, bank_deposit, correction, opening_balance
            $table->string('direction', 10); // in, out
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->nullable(); // cash, mobile_money, card, check, credit, bank_transfer
            $table->string('reference', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('movement_date');
            $table->timestamps();

            $table->index(['cash_register_id', 'type']);
            $table->index(['cash_register_id', 'movement_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
