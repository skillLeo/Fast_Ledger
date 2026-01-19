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
        Schema::create('vat_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('vrn', 15);
            $table->string('period_key', 20);
            $table->decimal('vat_due_sales', 15);
            $table->decimal('vat_due_acquisitions', 15);
            $table->decimal('total_vat_due', 15);
            $table->decimal('vat_reclaimed_curr_period', 15);
            $table->decimal('net_vat_due', 15);
            $table->decimal('total_value_sales_ex_vat', 15);
            $table->decimal('total_value_purchases_ex_vat', 15);
            $table->decimal('total_value_goods_supplied_ex_vat', 15);
            $table->decimal('total_acquisitions_ex_vat', 15);
            $table->unsignedBigInteger('submitted_by_user_id');
            $table->timestamp('submitted_at')->nullable();
            $table->json('hmrc_response')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('processing_date')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_submissions');
    }
};
