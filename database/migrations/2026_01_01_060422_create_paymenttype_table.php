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
        Schema::create('paymenttype', function (Blueprint $table) {
            $table->integer('Payment_Type_ID');
            $table->string('Payment_Type_Code', 20);
            $table->string('Payment_Type_Name', 50);
            $table->integer('Bank_Type_ID');
            $table->integer('Paid_In_Out')->nullable()->comment('1 paid in= 2 paid out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paymenttype');
    }
};
