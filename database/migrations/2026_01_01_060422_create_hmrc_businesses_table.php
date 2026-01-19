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
        Schema::create('hmrc_businesses', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('nino', 10)->nullable();
            $table->string('business_id', 20);
            $table->enum('type_of_business', ['self-employment', 'uk-property', 'foreign-property', 'property-unspecified']);
            $table->string('trading_name')->nullable();
            $table->enum('accounting_type', ['CASH', 'ACCRUAL', 'ACCRUALS'])->nullable();
            $table->date('commencement_date')->nullable();
            $table->date('cessation_date')->nullable();
            $table->enum('quarterly_period_type', ['standard', 'calendar'])->nullable();
            $table->string('tax_year_of_choice', 10)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->json('business_address_json')->nullable();
            $table->json('accounting_periods_json')->nullable();
            $table->json('metadata_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_businesses');
    }
};
