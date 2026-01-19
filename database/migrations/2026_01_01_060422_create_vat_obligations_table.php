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
        Schema::create('vat_obligations', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('vrn', 15);
            $table->string('period_key', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('due_date');
            $table->enum('status', ['O', 'F'])->default('O');
            $table->date('received_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vat_obligations');
    }
};
