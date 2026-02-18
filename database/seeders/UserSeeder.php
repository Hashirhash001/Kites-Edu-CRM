<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {

        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@kites.com',
            'password' => Hash::make('password123'),
            'phone' => '9999999999',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create Lead Manager
        User::create([
            'name' => 'Lead Manager',
            'email' => 'leadmanager@kites.com',
            'password' => Hash::make('password123'),
            'phone' => '8888888888',
            'role' => 'lead_manager',
            'is_active' => true,
        ]);

        // Create Field Staff
        User::create([
            'name' => 'Field Staff',
            'email' => 'fieldstaff@kites.com',
            'password' => Hash::make('password123'),
            'phone' => '7777777777',
            'role' => 'telecallers',
            'is_active' => true,
        ]);
    }
}
