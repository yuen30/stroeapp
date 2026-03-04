<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('purchase_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->integer('quantity');  // Actual received quantity
            $table->string('condition')->default('good');  // good, damaged, defective
            $table->integer('quantity_damaged')->default(0);  // จำนวนสินค้าชำรุด
            $table->integer('quantity_defective')->default(0);  // จำนวนสินค้าบกพร่อง
            $table->text('quality_notes')->nullable();  // หมายเหตุปัญหาที่พบ
            $table->json('images')->nullable();  // รูปภาพสินค้าที่รับมา
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
