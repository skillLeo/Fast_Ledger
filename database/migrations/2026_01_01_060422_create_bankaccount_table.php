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
        Schema::create('bankaccount', function (Blueprint $table) {
            $table->integer('Bank_Account_ID')->primary();
            $table->integer('Client_ID');
            $table->integer('Bank_Type_ID');
            $table->string('Bank_Name', 250);
            $table->string('Account_Name', 200)->nullable();
            $table->string('Account_No', 50)->nullable();
            $table->string('Sort_Code', 50)->nullable();
            $table->dateTime('Created_On')->nullable();
            $table->integer('Created_By')->nullable();
            $table->dateTime('Last_Modified_On')->nullable();
            $table->integer('Last_Modified_By')->nullable();
            $table->integer('Is_Deleted');
            $table->enum('entity_type', ['Client', 'Supplier'])->default('Supplier');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('finexer_account_id', 100)->nullable()->index('idx_finexer_account_id')->comment('External bank account ID from Finexer API');
            $table->string('finexer_institution_id', 100)->nullable()->comment('Bank institution ID from Finexer');
            $table->string('finexer_consent_id', 100)->nullable()->comment('Consent/authorization ID for bank access');
            $table->enum('bank_feed_status', ['not_connected', 'connected', 'expired', 'error'])->nullable()->default('not_connected')->index('idx_bank_feed_status')->comment('Current status of bank feed connection');
            $table->timestamp('bank_feed_connected_at')->nullable()->comment('When bank feed was first connected');
            $table->timestamp('bank_feed_last_synced_at')->nullable()->comment('Last successful transaction sync timestamp');
            $table->date('bank_feed_sync_from_date')->nullable()->comment('Start date for transaction syncing');
            $table->text('bank_feed_error')->nullable()->comment('Last error message from bank feed sync');
            $table->boolean('auto_sync_enabled')->nullable()->default(false)->comment('Enable automatic daily sync');
            $table->text('bank_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankaccount');
    }
};
