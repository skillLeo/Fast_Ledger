<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n👑 Super Admin create / check ho raha hai...\n";

        // 1️⃣ Super Admin check
        $admin = DB::table('user')
            ->where('User_Name', 'admin')
            ->first();

        if (!$admin) {

            // 2️⃣ Super Admin create
            $adminId = DB::table('user')->insertGetId([
                'Full_Name'  => 'Administrator',
                'User_Name'  => 'admin',
                'password'   => Hash::make('admin@123'),
                'email'      => 'khawarjavid@hotmail.com',
                'language'   => 'en',
                'Is_Active'  => 1,
                'Sys_IP'     => request()->ip() ?? '127.0.0.1',
                'User_Role'  => 1, // ADMIN / SUPER ADMIN
                'Client_ID'  => null,
                'Created_By' => 1,
                'Created_On' => now(),
                'Is_Archive' => 0,
            ]);

            echo "✅ Super Admin create ho gaya (User_ID: {$adminId})\n";

        } else {

            $adminId = $admin->User_ID;
            echo "ℹ️ Super Admin pehle se mojood hai (User_ID: {$adminId})\n";
        }

        // 3️⃣ Saare active modules nikaalo
        $modules = DB::table('modules')
            ->where('Is_Active', 1)
            ->get();

        echo "📦 {$modules->count()} module(s) mil gaye\n";

        // 4️⃣ Full access assign karo
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

                echo "   ✔ Access granted: {$module->Module_Display_Name}\n";
            }
        }

        echo "\n🎉 Super Admin ko COMPLETE SYSTEM ACCESS mil gaya\n\n";
    }
}