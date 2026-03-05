<?php

use App\Enums\DocumentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
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
        Schema::create('sale_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('company_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('created_by')->references('id')->on('users');
            $table->foreignUlid('salesman_id')->nullable()->references('id')->on('users');
            $table->string('document_type')->default(DocumentType::TaxInvoice->value);
            $table->string('invoice_number')->unique();
            $table->date('order_date');
            $table->date('due_date')->nullable();
            $table->string('term_of_payment')->nullable();
            $table->string('status')->default(OrderStatus::Draft->value);
            $table->string('payment_status')->default(PaymentStatus::Unpaid->value);
            $table->string('payment_method')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();  // ไฟล์แนบ
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('sale_order_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('sale_order_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_order_items');
        Schema::dropIfExists('sale_orders');
    }
};
