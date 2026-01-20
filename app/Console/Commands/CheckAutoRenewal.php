<?php
// app/Console/Commands/CheckAutoRenewal.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckAutoRenewal extends Command
{
    protected $signature = 'subscription:check-auto-renewal {email}';
    protected $description = 'Check auto-renewal status for a user';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info("🔍 Checking auto-renewal for: {$email}");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // ✅ Step 1: Check if column exists
        if (!Schema::hasColumn('user', 'auto_renewal')) {
            $this->error("❌ COLUMN MISSING: 'auto_renewal' column does not exist!");
            $this->warn("   Run: php artisan migrate");
            return 1;
        }

        $this->info("✅ Column 'auto_renewal' exists");

        // ✅ Step 2: Get column info
        $columnInfo = DB::select("SHOW COLUMNS FROM user LIKE 'auto_renewal'");
        if ($columnInfo) {
            $this->line("   Type: {$columnInfo[0]->Type}");
            $this->line("   Null: {$columnInfo[0]->Null}");
            $this->line("   Default: " . ($columnInfo[0]->Default ?? 'NULL'));
        }

        // ✅ Step 3: Get user data
        $user = DB::table('user')->where('email', $email)->first([
            'User_ID',
            'email',
            'subscription_status',
            'auto_renewal',
            'next_billing_date',
            'payment_frequency',
        ]);

        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        $this->line("\n📊 User Data:");
        $this->table(
            ['Field', 'Value'],
            [
                ['User_ID', $user->User_ID],
                ['Email', $user->email],
                ['Subscription Status', $user->subscription_status],
                ['Auto Renewal', $user->auto_renewal . ' (' . ($user->auto_renewal == 1 ? 'ENABLED' : 'DISABLED') . ')'],
                ['Payment Frequency', $user->payment_frequency ?? 'N/A'],
                ['Next Billing', $user->next_billing_date ?? 'N/A'],
            ]
        );

        // ✅ Step 4: Test toggle
        $this->line("\n🧪 Testing Toggle...");
        
        $currentValue = $user->auto_renewal;
        $newValue = $currentValue == 1 ? 0 : 1;

        $this->line("   Current: {$currentValue}");
        $this->line("   Will set to: {$newValue}");

        if (!$this->confirm('Proceed with test toggle?', false)) {
            $this->warn("❌ Test cancelled");
            return 0;
        }

        // Update
        $updated = DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->update([
                'auto_renewal' => $newValue,
                'Modified_On' => now(),
            ]);

        $this->line("   Rows updated: {$updated}");

        // Verify
        $verify = DB::table('user')
            ->where('User_ID', $user->User_ID)
            ->value('auto_renewal');

        $this->line("   Database value after update: {$verify}");

        if ($verify == $newValue) {
            $this->info("✅ Toggle TEST SUCCESSFUL!");
        } else {
            $this->error("❌ Toggle TEST FAILED!");
            $this->error("   Expected: {$newValue}");
            $this->error("   Got: {$verify}");
        }

        // Revert
        if ($this->confirm('Revert to original value?', true)) {
            DB::table('user')
                ->where('User_ID', $user->User_ID)
                ->update(['auto_renewal' => $currentValue]);
            $this->info("✅ Reverted to original value: {$currentValue}");
        }

        return 0;
    }
}