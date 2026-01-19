<?php
// database/migrations/YYYY_MM_DD_HHMMSS_add_stripe_and_trial_columns_to_users_table.php

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
        Schema::table('users', function (Blueprint $table) {
            // Add Stripe payment intent ID
            $table->string('stripe_payment_intent_id', 100)->nullable()->after('subscription_status');
            
            // Add free trial tracking
            $table->boolean('has_used_free_trial')->default(false)->after('stripe_payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent_id', 'has_used_free_trial']);
        });
    }
};