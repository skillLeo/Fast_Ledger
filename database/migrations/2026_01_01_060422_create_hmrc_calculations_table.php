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
        Schema::create('hmrc_calculations', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('nino', 9);
            $table->string('tax_year', 7);
            $table->string('calculation_id', 100);
            $table->timestamp('calculation_timestamp')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('request_intent', 50)->nullable();
            $table->decimal('total_income_received', 15)->nullable();
            $table->decimal('total_taxable_income', 15)->nullable();
            $table->decimal('income_tax_and_nics_due', 15)->nullable();
            $table->decimal('income_tax_nics_charged', 15)->nullable();
            $table->decimal('total_allowances_and_deductions', 15)->nullable();
            $table->decimal('total_student_loans_repayment_amount', 15)->nullable();
            $table->json('calculation_json')->nullable();
            $table->string('status', 20)->default('processing');
            $table->boolean('is_crystallised')->default(false);
            $table->timestamp('crystallised_at')->nullable();
            $table->json('crystallisation_response')->nullable();
            $table->text('error_message')->nullable();
            $table->json('messages')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_calculations');
    }
};
