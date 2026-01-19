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
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('client_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->string('name')->nullable();
            $table->enum('category', ['purchase', 'sales'])->comment('Product type: purchase or sales');
            $table->string('item_code', 50);
            $table->text('description');
            $table->unsignedBigInteger('ledger_id')->nullable();
            $table->string('account_ref', 100)->nullable();
            $table->decimal('unit_amount', 10)->default(0);
            $table->unsignedBigInteger('vat_rate_id')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
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
