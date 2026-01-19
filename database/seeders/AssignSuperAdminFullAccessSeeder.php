<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n👑 Creating / Verifying Super Admin...\n";

        // STEP 1: Create or fetch Super Admin
        $admin = DB::table('user')->where('User_Name', 'admin')->first();

        if (!$admin) {
            $adminId = DB::table('user')->insertGetId([
                'Full_Name'  => 'Administrator',
                'User_Name'  => 'admin',
                'password'   => Hash::make('admin@123'),
                'email'      => 'admin@example.com',
                'language'   => 'en',
                'Is_Active'  => 1,
                'User_Role'  => 1, // SUPER ADMIN
                'Created_By' => 1,
                'Created_On' => now(),
                'Is_Archive' => 0,
            ]);

            echo "✅ Super Admin created (ID: {$adminId})\n";
        } else {
            $adminId = $admin->User_ID;
            echo "ℹ️  Super Admin already exists (ID: {$adminId})\n";
        }

        // STEP 2: Get all active modules
        $modules = DB::table('modules')
            ->where('Is_Active', 1)
            ->get();

        echo "📦 Found {$modules->count()} modules\n";

        // STEP 3: Grant full access
        foreach ($modules as $module) {
            $exists = DB::table('user_module_access')
                ->where('User_ID', $adminId)
                ->where('Module_ID', $module->Module_ID)
                ->exists();

            if (!$exists) {
                DB::table('user_module_access')->insert([
                    'User_ID'    => $adminId,
                    'Module_ID'  => $module->Module_ID,
                    'Has_Access' => 1,
                    'Granted_At' => now(),
                    'Is_Active'  => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                echo "   ✓ Access granted to {$module->Module_Display_Name}\n";
            }
        }

        echo "🎉 Super Admin now has FULL ACCESS\n\n";
    }
}