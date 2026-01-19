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
        Schema::create('bankreconciliation', function (Blueprint $table) {
            $table->integer('Bank_Recon_ID')->primary();
            $table->decimal('Balance', 12)->nullable();
            $table->date('Balance_Date')->nullable();
            $table->decimal('Cash_Balance', 12)->nullable();
            $table->date('Cash_Balance_Date')->nullable();
            $table->integer('Created_By')->nullable();
            $table->dateTime('Created_On')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankreconciliation');
    }
};
