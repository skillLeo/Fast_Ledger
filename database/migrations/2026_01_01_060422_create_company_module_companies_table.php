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
        Schema::create('company_module_companies', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedInteger('User_ID')->comment('FK to user.User_ID - Company owner');
            $table->string('Company_Name')->comment('Required');
            $table->string('Trade_Name')->nullable()->comment('Optional');
            $table->string('Street_Address')->comment('Required');
            $table->string('City', 100)->comment('Required');
            $table->string('State', 100)->nullable()->comment('Optional - State/Province/Region');
            $table->string('Postal_Code', 20)->comment('Required');
            $table->string('Country', 2)->comment('Required - ISO country code (GB, ES, etc.)');
            $table->enum('Company_Type_ES', ['autonomo', 'sociedad_limitada', 'sociedad_anonima', 'cooperativa', 'sociedad_civil', 'comunidades_bienes', 'fundacion_asociacion', 'otra'])->nullable()->comment('For Spain - Company Type');
            $table->enum('Company_Type_UK', ['sole_trader', 'private_limited_company', 'public_limited_company', 'limited_liability_partnership', 'partnership', 'community_interest_company', 'charity', 'overseas_company', 'other'])->nullable()->comment('For UK - Company Type');
            $table->string('Tax_ID', 50)->comment('Required - NIF/CIF/VAT Number');
            $table->string('Country_Tax_Residence', 2)->comment('Required - ISO country code');
            $table->enum('Tax_Regime', ['regimen_general', 'regimen_simplificado', 'recargo_equivalencia', 'agricultura_ganaderia_pesca', 'grupo_iva', 'oss_ioss', 'estimacion_directa_objetiva', 'bienes_usados_arte_antiguos', 'otra'])->nullable()->comment('Only for Spain - Tax Regime Type');
            $table->string('Phone_Number', 50)->nullable();
            $table->string('Email', 100)->nullable();
            $table->string('Website')->nullable();
            $table->boolean('Verifactu_Enabled')->default(false)->comment('VERIFACTU Mode ON/OFF');
            $table->string('AEAT_Certificate_Path')->nullable()->comment('File upload path (PFX/PEM)');
            $table->string('SIF_Identifier', 100)->nullable()->comment('SIF / Identifier');
            $table->boolean('Is_Test_Mode')->default(true)->comment('Test/Production Mode toggle');
            $table->string('Logo_Path')->nullable()->comment('File upload path');
            $table->string('Currency', 3)->default('EUR')->comment('GBP for UK, EUR for Spain/others');
            $table->string('Invoice_Prefix', 10)->nullable()->comment('Invoice number prefix');
            $table->integer('Next_Invoice_Number')->default(1)->comment('Next invoice number to use');
            $table->boolean('Is_Active')->default(true);
            $table->enum('Subscription_Status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->timestamp('Subscription_End_Date')->nullable();
            $table->unsignedInteger('Created_By')->nullable();
            $table->timestamp('Created_On')->useCurrent();
            $table->unsignedInteger('Modified_By')->nullable();
            $table->timestamp('Modified_On')->useCurrentOnUpdate()->nullable();
            $table->unsignedInteger('Deleted_By')->nullable();
            $table->timestamp('Deleted_On')->nullable();
            $table->boolean('Is_Archive')->default(false);
            $table->boolean('Profile_Completed')->default(false);
            $table->integer('Profile_Completion_Percentage')->default(0);
            $table->timestamp('Last_Profile_Reminder_At')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_module_companies');
    }
};
