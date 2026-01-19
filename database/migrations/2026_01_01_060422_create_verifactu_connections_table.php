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
        Schema::create('verifactu_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('company_id');
            $table->string('name');
            $table->string('nif', 20);
            $table->string('company_name');
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->text('certificate_path')->nullable();
            $table->text('certificate_password')->nullable();
            $table->string('sif_id')->nullable();
            $table->enum('status', ['disconnected', 'connected', 'error'])->default('disconnected');
            $table->text('last_error')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifactu_connections');
    }
};
