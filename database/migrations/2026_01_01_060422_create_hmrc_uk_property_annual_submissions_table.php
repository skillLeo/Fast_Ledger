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
        Schema::create('hmrc_uk_property_annual_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('business_id');
            $table->unsignedBigInteger('obligation_id')->nullable();
            $table->string('nino', 9)->nullable();
            $table->string('tax_year', 7);
            $table->timestamp('submission_date')->nullable();
            $table->json('adjustments_json')->nullable();
            $table->json('allowances_json')->nullable();
            $table->json('response_json')->nullable();
            $table->enum('status', ['draft', 'submitted', 'failed'])->default('draft');
            $table->string('test_scenario')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_uk_property_annual_submissions');
    }
};
