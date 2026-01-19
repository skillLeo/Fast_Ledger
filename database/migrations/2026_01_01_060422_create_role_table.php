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
        Schema::create('role', function (Blueprint $table) {
            $table->integer('Role_ID')->comment('fRoleID identifies fAdminRole');
            $table->string('Role_Name', 50)->comment('fName is of fAdminRole');
            $table->string('Description', 250)->nullable()->comment('fDesc is of fAdminRole');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role');
    }
};
