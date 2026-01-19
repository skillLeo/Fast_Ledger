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
        Schema::create('invoices', function (Blueprint $table) {
            $table->integer('id');
            $table->string('customer');
            $table->string('customer_type');
            $table->bigInteger('company_id');
            $table->dateTime('invoice_date');
            $table->date('operation_date')->nullable()->comment('If different from invoice date');
            $table->dateTime('due_date');
            $table->string('invoice_no')->nullable();
            $table->enum('status', ['draft', 'sent', 'cancelled', 'paid', 'partially_paid', 'overdue'])->default('draft');
            $table->unsignedBigInteger('series_id')->nullable();
            $table->string('invoice_ref')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('net_amount', 15)->nullable();
            $table->decimal('vat_amount', 15)->nullable();
            $table->decimal('total_amount', 15)->nullable();
            $table->decimal('paid', 10)->nullable()->default(0);
            $table->decimal('balance', 10)->nullable()->default(0);
            $table->timestamp('created_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->useCurrent();
            $table->timestamp('issued_at')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
