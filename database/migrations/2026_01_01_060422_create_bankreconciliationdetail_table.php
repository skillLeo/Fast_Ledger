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
        Schema::create('bankreconciliationdetail', function (Blueprint $table) {
            $table->integer('Bank_Recon_Detail_ID')->primary();
            $table->integer('Transaction_ID');
            $table->integer('Bank_Recon_ID')->default(0);
            $table->string('Cheque', 20)->nullable();
            $table->string('Chq_Date')->nullable();
            $table->decimal('Amount', 12)->nullable();
            $table->integer('Add_Type')->nullable()->comment('1: Less (Interest Paid)                        
2: Less (Cheques is Transit)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankreconciliationdetail');
    }
};
