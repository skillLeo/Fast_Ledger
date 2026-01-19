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
        Schema::create('transaction', function (Blueprint $table) {
            $table->integer('Transaction_ID');
            $table->dateTime('Transaction_Date');
            $table->integer('File_ID')->nullable();
            $table->integer('Bank_Account_ID')->nullable();
            $table->integer('Paid_In_Out')->comment('1: paid in
2: paid out');
            $table->integer('Payment_Type_ID')->nullable();
            $table->integer('Account_Ref_ID')->nullable();
            $table->bigInteger('chart_of_account_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->dateTime('Inv_Due_Date')->nullable();
            $table->string('Cheque', 20)->nullable();
            $table->decimal('Amount', 12)->nullable();
            $table->enum('entry_type', ['Dr', 'Cr'])->nullable();
            $table->decimal('debit_amount', 15)->nullable();
            $table->decimal('credit_amount', 15)->nullable();
            $table->enum('account_type', ['Asset', 'Liability', 'Equity', 'Income', 'Expense'])->nullable();
            $table->string('Description', 500)->nullable();
            $table->integer('Is_Imported')->nullable()->default(0)->comment('0: not imported yet
1: imported');
            $table->integer('Created_By')->nullable();
            $table->dateTime('Created_On')->nullable();
            $table->integer('Modified_By')->nullable();
            $table->dateTime('Modified_On')->nullable();
            $table->integer('Deleted_By')->nullable();
            $table->dateTime('Deleted_On')->nullable();
            $table->integer('VAT_ID')->nullable();
            $table->boolean('Is_Bill')->default(false);
            $table->string('Transaction_Code', 50);
            $table->enum('source', ['manual', 'bank_feed'])->nullable()->default('manual')->comment('Source of transaction: manual upload or automatic bank feed');
            $table->string('finexer_transaction_id', 100)->nullable()->comment('External transaction ID from Finexer API');
            $table->string('finexer_reference')->nullable()->comment('Bank reference number from Finexer');
            $table->timestamp('bank_feed_synced_at')->nullable()->comment('When this transaction was synced from bank feed');
            $table->string('journal_entry_ref', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
