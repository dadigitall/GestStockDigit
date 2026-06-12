<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_register_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['cash_register_id', 'user_id']);
        });

        Schema::table('cash_registers', function (Blueprint $table) {
            $table->text('cashier_signature')->nullable()->after('closing_note');
            $table->text('validator_signature')->nullable()->after('validated_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_user');

        Schema::table('cash_registers', function (Blueprint $table) {
            $table->dropColumn(['cashier_signature', 'validator_signature']);
        });
    }
};
