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
        Schema::create('hmrc_final_declarations', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('nino', 10);
            $table->string('tax_year', 10);
            $table->enum('wizard_step', ['prerequisites_check', 'review_submissions', 'review_calculation', 'review_income', 'declaration', 'completed'])->default('prerequisites_check');
            $table->json('prerequisites_check')->nullable();
            $table->boolean('prerequisites_passed')->default(false);
            $table->boolean('submissions_reviewed')->default(false);
            $table->timestamp('submissions_reviewed_at')->nullable();
            $table->boolean('calculation_reviewed')->default(false);
            $table->timestamp('calculation_reviewed_at')->nullable();
            $table->boolean('income_reviewed')->default(false);
            $table->timestamp('income_reviewed_at')->nullable();
            $table->boolean('declaration_confirmed')->default(false);
            $table->timestamp('declaration_confirmed_at')->nullable();
            $table->text('declaration_ip_address')->nullable();
            $table->text('declaration_user_agent')->nullable();
            $table->unsignedBigInteger('calculation_id')->nullable();
            $table->enum('status', ['draft', 'ready', 'submitting', 'submitted', 'failed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->json('submission_response')->nullable();
            $table->json('submission_errors')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_final_declarations');
    }
};
