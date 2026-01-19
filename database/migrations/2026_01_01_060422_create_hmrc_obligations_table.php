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
        Schema::create('hmrc_obligations', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->integer('user_id');
            $table->string('business_id', 20);
            $table->enum('type_of_business', ['self-employment', 'uk-property', 'foreign-property', 'property-unspecified']);
            $table->enum('obligation_type', ['periodic', 'crystallisation']);
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->date('due_date');
            $table->enum('status', ['open', 'fulfilled'])->default('open');
            $table->date('received_date')->nullable();
            $table->string('period_key')->nullable();
            $table->string('quarter', 10)->nullable();
            $table->string('tax_year', 10)->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->integer('days_until_due')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('submission_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_obligations');
    }
};
