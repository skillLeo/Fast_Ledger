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
        Schema::create('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('User_ID');
            $table->unsignedBigInteger('Company_ID');
            $table->enum('Customer_Type', ['Individual', 'Company']);
            $table->string('Legal_Name_Company_Name');
            $table->enum('Tax_ID_Type', ['NIF', 'CIF', 'NIE', 'EU_VAT']);
            $table->string('Tax_ID_Number', 50);
            $table->string('Street_Address');
            $table->string('City');
            $table->string('Postal_Code', 20);
            $table->string('Province');
            $table->string('Country');
            $table->string('Email');
            $table->string('Phone', 30)->nullable();
            $table->string('Contact_Person_Name')->nullable();
            $table->boolean('Has_VAT')->default(false);
            $table->enum('VAT_Rate', ['Standard_21', 'Reduced_10', 'Super_Reduced_4', 'Exempt_0', 'Intra_EU', 'Export'])->nullable();
            $table->boolean('Has_IRPF')->default(false);
            $table->enum('IRPF_Rate', ['7', '15'])->nullable();
            $table->enum('Payment_Method', ['Bank_Transfer', 'Cash']);
            $table->string('IBAN', 50)->nullable();
            $table->string('Bank_Name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
