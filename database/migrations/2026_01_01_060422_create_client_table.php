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
        Schema::create('client', function (Blueprint $table) {
            $table->integer('Client_ID')->primary();
            $table->string('Client_Ref', 20)->unique('client_ref');
            $table->string('Contact_Name', 50);
            $table->string('Business_Name', 50)->nullable();
            $table->string('Address1');
            $table->string('Address2')->nullable();
            $table->string('Town', 50);
            $table->integer('Country_ID');
            $table->string('Post_Code', 150);
            $table->string('Phone', 20);
            $table->string('Mobile', 20)->nullable();
            $table->string('Fax', 20)->nullable();
            $table->string('Email', 150)->nullable();
            $table->string('Company_Reg_No', 20)->nullable();
            $table->string('VAT_Registration_No', 20)->nullable();
            $table->string('Contact_No', 20)->nullable();
            $table->decimal('Fee_Agreed', 12)->nullable();
            $table->integer('Created_By')->nullable();
            $table->dateTime('Created_On')->nullable();
            $table->integer('Modified_By')->nullable();
            $table->dateTime('Modified_On')->nullable();
            $table->integer('Deleted_By')->nullable();
            $table->dateTime('Deleted_On')->nullable();
            $table->integer('Is_Archive');
            $table->date('date_lock')->nullable();
            $table->date('transaction_lock')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client');
    }
};
