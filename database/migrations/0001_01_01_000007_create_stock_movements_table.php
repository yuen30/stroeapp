<?php

use App\Enums\StockMovementType;
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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->foreignUlid('sale_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('created_by')->references('id')->on('users');
            $table->string('type')->default(StockMovementType::In->value);
            $table->integer('quantity');
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
