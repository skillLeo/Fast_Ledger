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
        Schema::create('modules', function (Blueprint $table) {
            $table->unsignedBigInteger('Module_ID');
            $table->string('Module_Name', 50)->comment('Unique module identifier (e.g., fast_ledger, company_module)');
            $table->string('Module_Display_Name', 100)->comment('Display name for UI');
            $table->string('Description', 250)->nullable();
            $table->string('Module_Icon', 50)->nullable()->comment('Icon class or path');
            $table->string('Module_Route', 100)->nullable()->comment('Base route for module');
            $table->boolean('Is_Active')->default(true)->comment('Module can be enabled/disabled by Super Admin');
            $table->integer('Display_Order')->default(0)->comment('Order in module list');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
