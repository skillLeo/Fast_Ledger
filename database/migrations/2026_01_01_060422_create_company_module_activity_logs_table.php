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
        Schema::create('company_module_activity_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('Company_ID')->comment('FK to company_module_companies.id');
            $table->unsignedInteger('User_ID')->comment('FK to user.User_ID - Who performed the action');
            $table->string('Activity_Type', 50)->comment('e.g., invoice_created, invoice_edited, invoice_issued, invoice_sent, invoice_cancelled, user_login, settings_updated');
            $table->string('Entity_Type', 50)->comment('e.g., invoice, customer, item, company, user');
            $table->unsignedBigInteger('Entity_ID')->nullable()->comment('ID of the affected record');
            $table->text('Description')->comment('Human-readable description of the action');
            $table->string('IP_Address', 45)->comment('User IP address (supports IPv6)');
            $table->text('User_Agent')->nullable()->comment('Browser/device information');
            $table->string('Request_Method', 10)->nullable()->comment('GET, POST, PUT, DELETE');
            $table->string('Request_URL', 500)->nullable()->comment('Full URL of the request');
            $table->json('Old_Values')->nullable()->comment('Values before changes (JSON)');
            $table->json('New_Values')->nullable()->comment('Values after changes (JSON)');
            $table->timestamp('Created_At')->useCurrent()->comment('Timestamp with timezone');
            $table->string('Timezone', 50)->default('UTC')->comment('Timezone when action occurred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_module_activity_logs');
    }
};
