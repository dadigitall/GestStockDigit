<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('invoice_prefix')->default('FAC');
            $table->string('sale_prefix')->default('VENTE');
            $table->string('purchase_prefix')->default('CF');
            $table->string('delivery_prefix')->default('BL');
            $table->string('quotation_prefix')->default('DEV');
            $table->string('credit_note_prefix')->default('AVOIR');
            $table->string('transfer_prefix')->default('TR');
            $table->text('invoice_footer')->nullable();
            $table->text('invoice_terms')->nullable();
            $table->text('ticket_footer')->nullable();
            $table->boolean('enable_multi_currency')->default(false);
            $table->string('secondary_currency', 3)->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->decimal('discount_max_rate', 5, 2)->default(100);
            $table->decimal('credit_limit_default', 15, 2)->default(0);
            $table->integer('alert_threshold_global')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_prefix', 'sale_prefix', 'purchase_prefix',
                'delivery_prefix', 'quotation_prefix', 'credit_note_prefix',
                'transfer_prefix', 'invoice_footer', 'invoice_terms',
                'ticket_footer', 'enable_multi_currency', 'secondary_currency',
                'default_tax_rate', 'discount_max_rate', 'credit_limit_default',
                'alert_threshold_global',
            ]);
        });
    }
};
