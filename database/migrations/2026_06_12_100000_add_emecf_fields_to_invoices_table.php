<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Link to the package's emecf_invoices table
            $table->unsignedBigInteger('emecf_invoice_id')->nullable()->after('sale_id');
            $table->string('emecf_uid', 100)->nullable()->after('emecf_invoice_id');
            $table->string('emecf_code', 100)->nullable()->after('emecf_uid');
            $table->text('emecf_qr_code')->nullable()->after('emecf_code');
            $table->string('emecf_status', 20)->nullable()->after('emecf_qr_code'); // pending, confirmed, cancelled
            $table->timestamp('emecf_sent_at')->nullable()->after('emecf_status');

            $table->foreign('emecf_invoice_id')
                  ->references('id')
                  ->on('emecf_invoices')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['emecf_invoice_id']);
            $table->dropColumn([
                'emecf_invoice_id',
                'emecf_uid',
                'emecf_code',
                'emecf_qr_code',
                'emecf_status',
                'emecf_sent_at',
            ]);
        });
    }
};
