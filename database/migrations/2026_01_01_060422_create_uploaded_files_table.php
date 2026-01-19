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
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('file_path');
            $table->enum('status', ['pending_mapping', 'mapped', 'processing', 'completed', 'failed'])->default('pending_mapping');
            $table->json('column_mapping')->nullable();
            $table->json('file_headers')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
