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
        Schema::create('vat_form_labels', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('vat_type_id');
            $table->string('form_key');
            $table->string('display_name');
            $table->integer('percentage');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_form_labels');
    }
};
