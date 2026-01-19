<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignExistingUsersModuleAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder assigns Fast Ledger (Main App) module access to ALL existing users.
     * This ensures existing 10K+ users continue working without disruption.
     */
    public function run(): void
    {
        echo "\n🚀 Starting module access assignment for existing users...\n\n";

        // Step 1: Get Fast Ledger module ID
        $fastLedgerModule = DB::table('modules')
            ->where('Module_Name', 'fast_ledger')
            ->first();

        if (!$fastLedgerModule) {
            echo "❌ ERROR: Fast Ledger module not found!\n";
            echo "   Please run: php artisan migrate first\n\n";
            return;
        }

        echo "✅ Fast Ledger Module ID: {$fastLedgerModule->Module_ID}\n\n";

        // Step 2: Get all existing users who DON'T have module access yet
        $existingUsers = DB::table('user')
            ->select('User_ID', 'User_Name', 'User_Role', 'email')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('user_module_access')
                    ->whereRaw('user_module_access.User_ID = user.User_ID');
            })
            ->where('Is_Active', 1) // Only active users
            ->where('Is_Archive', 0) // Exclude archived users
            ->get();

        $totalUsers = $existingUsers->count();

        if ($totalUsers === 0) {
            echo "ℹ️  No users found without module access.\n";
            echo "   All users already have module access assigned.\n\n";
            return;
        }

        echo "📊 Found {$totalUsers} users without module access\n";
        echo "🔄 Assigning Fast Ledger access to all users...\n\n";

        // Step 3: Prepare batch insert data
        $moduleAccessData = [];
        $batchSize = 1000; // Process in batches of 1000
        $processedCount = 0;

        foreach ($existingUsers as $user) {
            $moduleAccessData[] = [
                'User_ID' => $user->User_ID,
                'Module_ID' => $fastLedgerModule->Module_ID,
                'Has_Access' => true,
                'Granted_By' => null, // Auto-granted during migration
                'Granted_At' => now(),
                'Revoked_At' => null,
                'Is_Active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert in batches for better performance
            if (count($moduleAccessData) >= $batchSize) {
                DB::table('user_module_access')->insert($moduleAccessData);
                $processedCount += count($moduleAccessData);
                
                echo "   ✓ Processed: {$processedCount}/{$totalUsers} users\n";
                
                $moduleAccessData = []; // Reset for next batch
            }
        }

        // Insert remaining records
        if (count($moduleAccessData) > 0) {
            DB::table('user_module_access')->insert($moduleAccessData);
            $processedCount += count($moduleAccessData);
            echo "   ✓ Processed: {$processedCount}/{$totalUsers} users\n";
        }

        echo "\n✅ SUCCESS! Module access assigned to {$totalUsers} users\n\n";

        // Step 4: Show summary by User_Role
        echo "📋 SUMMARY BY USER ROLE:\n";
        echo "─────────────────────────────────────────\n";

        $summary = DB::table('user')
            ->join('user_module_access', 'user.User_ID', '=', 'user_module_access.User_ID')
            ->where('user_module_access.Module_ID', $fastLedgerModule->Module_ID)
            ->select('user.User_Role', DB::raw('COUNT(*) as count'))
            ->groupBy('user.User_Role')
            ->get();

        $roleNames = [
            1 => 'Super Admin',
            2 => 'Admin',
            3 => 'Client',
            4 => 'Fee Earner',
            5 => 'Company User',
        ];

        foreach ($summary as $role) {
            $roleName = $roleNames[$role->User_Role] ?? "Unknown Role {$role->User_Role}";
            echo "   {$roleName}: {$role->count} users\n";
        }

        echo "\n─────────────────────────────────────────\n";
        echo "✨ All existing users now have Fast Ledger access!\n";
        echo "🔒 Company Module access is NOT granted (must be assigned manually)\n\n";

        // Step 5: Log the seeding action
        Log::info('Module access seeder completed', [
            'total_users' => $totalUsers,
            'module' => 'fast_ledger',
            'seeded_at' => now(),
        ]);
    }
}