<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete()->after('phone');
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete()->after('company_id');
            $table->boolean('is_active')->default(true)->after('store_id');
            $table->string('photo')->nullable()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['store_id']);
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'company_id',
                'store_id', 'is_active', 'photo', 'last_login_at',
            ]);
        });
    }
};
