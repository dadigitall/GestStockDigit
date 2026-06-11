<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type'); // shop, warehouse, depot, boutique
            $table->foreignId('parent_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('opening_hours')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('allows_stock')->default(true);
            $table->boolean('allows_sales')->default(true);
            $table->boolean('allows_cash_register')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
