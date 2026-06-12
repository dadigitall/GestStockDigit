<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('decision')->nullable()->after('status'); // approved, rejected
            $table->text('justification')->nullable()->after('decision');
            $table->foreignId('decided_by')->nullable()->after('justification')->constrained('users');
            $table->timestamp('decided_at')->nullable()->after('decided_by');
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->boolean('freeze_stock')->default(false)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['decision', 'justification', 'decided_by', 'decided_at']);
        });
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('freeze_stock');
        });
    }
};
