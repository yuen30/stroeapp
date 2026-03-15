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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('code')->unique();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('sale_order_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('sale_order_item_id')->constrained()->cascadeOnDelete();
            $table->integer('reserved_quantity')->unsigned();
            $table->timestamp('expires_at');
            $table->timestamps();

            // Indexes for performance
            $table->index('product_id');
            $table->index('expires_at');
            $table->index(['sale_order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
