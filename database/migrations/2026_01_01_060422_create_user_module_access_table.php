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
        Schema::create('user_module_access', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedInteger('User_ID')->comment('FK to user.User_ID');
            $table->unsignedBigInteger('Module_ID')->comment('FK to modules.Module_ID');
            $table->boolean('Has_Access')->default(false)->comment('User can access this module');
            $table->unsignedInteger('Granted_By')->nullable()->comment('FK to user.User_ID - who granted access');
            $table->timestamp('Granted_At')->nullable();
            $table->timestamp('Revoked_At')->nullable();
            $table->boolean('Is_Active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_module_access');
    }
};
