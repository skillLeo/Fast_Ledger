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
        Schema::create('file', function (Blueprint $table) {
            $table->integer('File_ID');
            $table->integer('Client_ID');
            $table->dateTime('File_Date');
            $table->string('Ledger_Ref', 20);
            $table->string('Matter', 20);
            $table->string('Sub_Matter', 20)->nullable();
            $table->string('Fee_Earner', 50)->nullable();
            $table->decimal('Fee_Agreed', 12)->nullable();
            $table->string('Referral_Name', 50)->nullable();
            $table->decimal('Referral_Fee', 12)->nullable();
            $table->string('First_Name', 50);
            $table->string('Last_Name', 50);
            $table->string('Address1')->nullable();
            $table->string('Address2')->nullable();
            $table->string('Town', 50)->nullable();
            $table->integer('Country_ID')->nullable();
            $table->string('Post_Code', 150)->nullable();
            $table->string('Phone', 20)->nullable();
            $table->string('Mobile', 20)->nullable();
            $table->string('Email', 150)->nullable();
            $table->date('Date_Of_Birth')->nullable();
            $table->string('NIC_No', 20)->nullable();
            $table->dateTime('Key_Date')->nullable();
            $table->string('Special_Note', 500)->nullable();
            $table->string('Status', 1)->nullable()->comment('L: Live

C: Close					
A: Abortive					
I: Close Abortive');
            $table->integer('Created_By')->nullable();
            $table->dateTime('Created_On')->nullable();
            $table->integer('Modified_By')->nullable();
            $table->dateTime('Modified_On')->nullable();
            $table->integer('Deleted_By')->nullable();
            $table->dateTime('Deleted_On')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file');
    }
};
