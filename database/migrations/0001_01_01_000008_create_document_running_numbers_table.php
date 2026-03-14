<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_running_numbers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_type'); // e.g., sale_order, purchase_order, tax_invoice
            $table->string('prefix')->nullable(); // e.g., INV, PO, TI
            $table->string('date_format')->nullable(); // e.g., Ym (2403)
            $table->integer('running_length')->default(4); // e.g., 4 = 0001
            $table->integer('current_number')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'branch_id', 'document_type'], 'drn_co_br_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_running_numbers');
    }
};
