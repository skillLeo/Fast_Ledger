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
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('code')->nullable();
            $table->string('ledger_ref');
            $table->string('account_ref');
            $table->enum('account_category', ['Income', 'Cost_of_Sales', 'Expenses', 'Taxation', 'Fixed_Assets', 'Current_Assets', 'Current_Liabilities', 'Non_Current_Liabilities', 'Share_Capital', 'Reserves', 'Retained_Earnings'])->nullable();
            $table->enum('normal_balance', ['Dr', 'Cr'])->nullable();
            $table->enum('money_flow_type', ['Money_In', 'Money_Out'])->nullable();
            $table->string('account_type')->nullable();
            $table->enum('pl_bs', ['P&L', 'BS'])->nullable();
            $table->integer('vat_id')->nullable()->index('idx_coa_vat');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['account_category', 'normal_balance']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
