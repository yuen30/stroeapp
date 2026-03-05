<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tax_invoices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('sale_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('created_by')->references('id')->on('users');
            $table->string('tax_invoice_number')->unique();
            $table->date('document_date');
            $table->string('customer_name');
            $table->string('customer_tax_id', 13)->nullable();
            $table->string('customer_address_line1', 500)->nullable();
            $table->string('customer_address_line2', 500)->nullable();
            $table->string('customer_amphoe', 100)->nullable();
            $table->string('customer_province', 100)->nullable();
            $table->string('customer_postal_code', 5)->nullable();
            $table->boolean('customer_is_head_office')->default(true);
            $table->string('customer_branch_no', 10)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('vat_rate')->default(7);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('payment_status')->default(PaymentStatus::Unpaid->value);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_invoices');
    }
};
