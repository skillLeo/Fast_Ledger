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
        Schema::create('hmrc_uk_property_period_summaries', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('business_id');
            $table->unsignedBigInteger('obligation_id')->nullable();
            $table->string('nino', 9)->nullable();
            $table->string('tax_year', 7);
            $table->string('submission_id', 36)->nullable();
            $table->date('from_date');
            $table->date('to_date');
            $table->json('fhl_income_json')->nullable();
            $table->json('fhl_expenses_json')->nullable();
            $table->json('non_fhl_income_json')->nullable();
            $table->json('non_fhl_expenses_json')->nullable();
            $table->json('uk_property_income_json')->nullable();
            $table->json('uk_property_expenses_json')->nullable();
            $table->json('response_json')->nullable();
            $table->enum('status', ['draft', 'submitted', 'failed'])->default('draft');
            $table->string('test_scenario')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_uk_property_period_summaries');
    }
};
