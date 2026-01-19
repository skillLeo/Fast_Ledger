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
        Schema::create('draft_invoice_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('item_code')->nullable();
            $table->text('description');
            $table->unsignedBigInteger('chart_of_account_id');
            $table->string('ledger_ref')->nullable()->comment('Cached from chart_of_accounts');
            $table->string('account_ref')->nullable()->comment('Cached from chart_of_accounts');
            $table->decimal('unit_amount', 15);
            $table->decimal('vat_rate', 5)->default(0);
            $table->decimal('vat_amount', 15)->default(0);
            $table->decimal('net_amount', 15)->comment('unit_amount + vat_amount');
            $table->unsignedBigInteger('vat_form_label_id')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_invoice_items');
    }
};
