<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('lot_id')->nullable()->constrained();
            $table->decimal('theoretical_quantity', 15, 2)->default(0);
            $table->decimal('physical_quantity', 15, 2)->nullable();
            $table->decimal('discrepancy_quantity', 15, 2)->default(0);
            $table->decimal('discrepancy_value', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, counted, approved, rejected
            $table->foreignId('counted_by')->nullable()->constrained('users');
            $table->timestamp('counted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
