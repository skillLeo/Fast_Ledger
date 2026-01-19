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
        Schema::create('roleprivileges', function (Blueprint $table) {
            $table->integer('Role_ID')->comment('fRoleID identifies tbAdminRolePrivileges');
            $table->integer('Privilege_ID')->comment('fPrivilegeID partly identifies tbAdminRolePrivileges');
            $table->integer('Form_ID')->comment('fFormID partly identifies tbAdminRolePrivileges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roleprivileges');
    }
};
