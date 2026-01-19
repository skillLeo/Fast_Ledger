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
        Schema::create('user', function (Blueprint $table) {
            $table->integer('User_ID')->comment('fUserID identifies tbAdminUser');
            $table->string('Full_Name', 50)->nullable()->comment('fFullName is of tbAdminUser');
            $table->string('User_Name', 50)->comment('fUserName is of tbAdminUser');
            $table->string('password')->nullable()->comment('fPassword is of tbAdminUser');
            $table->string('email', 50);
            $table->boolean('Is_Active')->nullable()->comment('fIsActive is of tbAdminUser');
            $table->string('Sys_IP', 50)->nullable()->comment('fSysIP is of tbAdminUser');
            $table->dateTime('Last_Login_DateTime')->nullable()->comment('fLastLoginDateTime is of tbAdminUser');
            $table->integer('User_Role')->comment('1: Administrator
2: Client');
            $table->integer('Client_ID')->nullable();
            $table->integer('Created_By')->nullable()->comment('fCreatedBy is of tbAdminUser');
            $table->dateTime('Created_On')->nullable()->comment('fCreatedOn is of tbAdminUser');
            $table->integer('Modified_By')->nullable()->comment('fModifiedBy is of tbAdminUser');
            $table->dateTime('Modified_On')->nullable()->comment('fModifiedOn is of tbAdminUser');
            $table->integer('Deleted_By')->nullable()->comment('fModifiedOn is of tbAdminUser');
            $table->dateTime('Deleted_On')->nullable()->comment('fModifiedOn is of tbAdminUser');
            $table->integer('Is_Archive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
