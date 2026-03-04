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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->longText('description')->nullable();
            $table->foreignUlid('company_id')->references('id')->on('companies');
            $table->foreignUlid('branch_id')->references('id')->on('branches');
            $table->foreignUlid('unit_id')->references('id')->on('units');
            $table->foreignUlid('brand_id')->references('id')->on('brands');
            $table->foreignUlid('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(0);  // จำนวน stock ขั้นต่ำสำหรับแจ้งเตือน
            $table->integer('max_stock')->default(0);  // จำนวน stock สูงสุด
            $table->string('barcode')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
