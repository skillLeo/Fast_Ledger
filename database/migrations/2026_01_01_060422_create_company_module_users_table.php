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
        Schema::create('company_module_users', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('Company_ID')->comment('FK to company_module_companies.id');
            $table->unsignedInteger('User_ID')->comment('FK to user.User_ID');
            $table->enum('Role', ['owner', 'admin', 'accountant', 'viewer'])->default('viewer')->comment('owner=full access, admin=manage users, accountant=create invoices, viewer=read only');
            $table->boolean('Is_Primary')->default(false)->comment('Primary contact for this company');
            $table->unsignedInteger('Invited_By')->nullable()->comment('FK to user.User_ID - who invited this user');
            $table->timestamp('Invited_At')->nullable();
            $table->timestamp('Accepted_At')->nullable();
            $table->string('Invitation_Token', 100)->nullable();
            $table->timestamp('Invitation_Expires_At')->nullable();
            $table->boolean('Is_Active')->default(true);
            $table->timestamp('Created_At')->useCurrent();
            $table->timestamp('Updated_At')->useCurrentOnUpdate()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_module_users');
    }
};
