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
        Schema::create('hmrc_periodic_submissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('business_id');
            $table->unsignedBigInteger('obligation_id')->nullable();
            $table->string('period_id')->nullable();
            $table->string('nino')->nullable();
            $table->string('tax_year', 10)->nullable();
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->timestamp('submission_date')->nullable();
            $table->json('income_json')->nullable();
            $table->json('expenses_json')->nullable();
            $table->json('response_json')->nullable();
            $table->enum('status', ['draft', 'submitted', 'failed'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_periodic_submissions');
    }
};
