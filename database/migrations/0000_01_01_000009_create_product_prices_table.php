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
        Schema::create('product_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained()->cascadeOnDelete();
            $table->string('price_type'); // e.g., retail, wholesale, mechanic, garage
            $table->string('name')->nullable(); // e.g., ราคาปลีก, ราคาช่าง
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('minimum_quantity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            // Allow one product to have unique price types
            $table->unique(['product_id', 'price_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
