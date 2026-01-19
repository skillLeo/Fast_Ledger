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
        Schema::create('branch', function (Blueprint $table) {
            $table->integer('Branch_ID')->primary();
            $table->integer('Bank_ID');
            $table->string('Branch_Name', 50);
            $table->integer('Created_By')->nullable();
            $table->dateTime('Created_On')->nullable();
            $table->integer('Modified_By')->nullable();
            $table->dateTime('Modified_On')->nullable();
            $table->integer('Deleted_By')->nullable();
            $table->dateTime('Deleted_On')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch');
    }
};
