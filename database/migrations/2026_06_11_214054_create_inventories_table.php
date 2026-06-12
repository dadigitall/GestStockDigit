<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('reference')->unique();
            $table->string('title');
            $table->string('type'); // global, partial, by_store, by_category, by_location, tournant, by_lot
            $table->string('status')->default('draft'); // draft, in_progress, frozen, completed, validated, cancelled
            $table->foreignId('store_id')->nullable()->constrained();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->integer('total_items')->default(0);
            $table->integer('total_discrepancies')->default(0);
            $table->decimal('total_discrepancy_value', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
