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
        Schema::create('privilege', function (Blueprint $table) {
            $table->integer('Privilege_ID')->comment('fPrivilegeID identifies tbAdminPrivileges');
            $table->string('Privilege_Code', 10)->comment('fCode is of tbAdminPrivileges');
            $table->string('Privilege_Name', 50)->nullable()->comment('fName is of tbAdminPrivileges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('privilege');
    }
};
