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
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('company_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('address_0')->nullable();
            $table->string('address_1')->nullable();
            $table->string('amphoe')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('tel')->nullable();
            $table->string('fax')->nullable();
            $table->string('tax_id',13)->nullable();
            $table->integer('credit_limit')->default(0);
            $table->integer('credit_days')->default(0);
            $table->integer('vat_rate')->default(7);
            $table->boolean('is_head_office')->default(true);
            $table->string('branch_no')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('address_0')->nullable();
            $table->string('address_1')->nullable();
            $table->string('amphoe')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('email')->nullable();
            $table->string('tel')->nullable();
            $table->string('fax')->nullable();
            $table->string('tax_id',13)->nullable();
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
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('customers');
    }
};
