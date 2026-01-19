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
        Schema::create('hmrc_tokens', function (Blueprint $table) {
            $table->integer('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('vrn', 15);
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at')->useCurrentOnUpdate()->useCurrent();
            $table->string('token_type')->default('Bearer');
            $table->string('scope')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hmrc_tokens');
    }
};
