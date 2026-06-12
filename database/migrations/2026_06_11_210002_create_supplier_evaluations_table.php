<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluated_by')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('respect_delays')->nullable();
            $table->tinyInteger('product_quality')->nullable();
            $table->tinyInteger('return_rate')->nullable();
            $table->tinyInteger('average_price')->nullable();
            $table->tinyInteger('reliability')->nullable();
            $table->tinyInteger('purchase_volume')->nullable();
            $table->decimal('overall_rating', 3, 1)->nullable();
            $table->text('comment')->nullable();
            $table->date('evaluated_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_evaluations');
    }
};
