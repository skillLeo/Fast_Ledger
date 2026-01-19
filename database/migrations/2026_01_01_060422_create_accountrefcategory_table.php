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
        Schema::create('accountrefcategory', function (Blueprint $table) {
            $table->integer('Category_ID')->primary();
            $table->string('Category_Name', 150);
            $table->dateTime('Effective_Date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accountrefcategory');
    }
};
