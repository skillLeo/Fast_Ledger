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
        Schema::create('accountref', function (Blueprint $table) {
            $table->integer('Account_Ref_ID')->primary();
            $table->string('Reference', 50);
            $table->integer('Bank_Type_ID')->nullable();
            $table->integer('Base_Category_ID')->nullable()->comment('1: Bill
2: Disb
3: Misc');
            $table->integer('Paid_In_Out')->nullable()->comment('1: in 2: out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accountref');
    }
};
