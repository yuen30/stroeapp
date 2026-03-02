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
        Schema::create('branches', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('company_id')->references('id')->on('companies');
            $table->boolean('is_headquarter')->default(false);
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
        Schema::dropIfExists('branches');
    }
};
