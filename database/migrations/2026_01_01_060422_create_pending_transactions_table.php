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
        Schema::create('pending_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('uploaded_file_id');
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('emoji')->nullable();
            $table->string('category')->nullable();
            $table->decimal('amount', 15)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('local_amount', 15)->nullable();
            $table->string('local_currency', 10)->nullable();
            $table->text('notes_and_tags')->nullable();
            $table->text('address')->nullable();
            $table->string('receipt')->nullable();
            $table->text('description')->nullable();
            $table->string('category_split')->nullable();
            $table->decimal('money_out', 15)->nullable();
            $table->decimal('money_in', 15)->nullable();
            $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->enum('source', ['manual', 'bank_feed'])->nullable()->default('manual')->comment('Source: manual upload or bank feed sync');
            $table->string('finexer_transaction_id', 100)->nullable()->comment('Unique transaction ID from Finexer API - prevents duplicates');
            $table->string('finexer_reference')->nullable()->comment('Bank reference number from statement');
            $table->json('raw_data')->nullable();
            $table->json('finexer_raw_data')->nullable()->comment('Complete raw response from Finexer API');
            $table->timestamp('bank_feed_fetched_at')->nullable()->comment('When this transaction was fetched from bank feed');
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_transactions');
    }
};
