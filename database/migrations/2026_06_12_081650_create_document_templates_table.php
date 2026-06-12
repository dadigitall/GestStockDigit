<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // quotation, invoice, delivery_note, customer_order
            $table->string('logo')->nullable();
            $table->json('colors')->nullable();
            $table->text('header_html')->nullable();
            $table->text('footer_html')->nullable();
            $table->text('legal_mentions')->nullable();
            $table->text('terms')->nullable();
            $table->string('paper_format')->default('A4');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
