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
        // Fetch branches
        $kollengode = Branch::where('code', 'KLG')->first();
        $palakkad   = Branch::where('code', 'PKD')->first();
        $thrissur   = Branch::where('code', 'TCR')->first();

        // ── Super Admin (no branch required) ─────────────────────────
        User::updateOrCreate(
            ['email' => 'superadmin@kites.com'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password123'),
                'phone'     => '9999999999',
                'role'      => 'super_admin',
                'branch_id' => null,
                'is_active' => true,
            ]
        );

        // ── Operation Head (no branch required) ───────────────────────
        User::updateOrCreate(
            ['email' => 'operationhead@kites.com'],
            [
                'name'      => 'Operation Head',
                'password'  => Hash::make('password123'),
                'phone'     => '8888888888',
                'role'      => 'operation_head',
                'branch_id' => null,
                'is_active' => true,
            ]
        );

        // ── Lead Managers (one per branch) ────────────────────────────
        User::updateOrCreate(
            ['email' => 'manager.kollengode@kites.com'],
            [
                'name'      => 'Manager Kollengode',
                'password'  => Hash::make('password123'),
                'phone'     => '7777777701',
                'role'      => 'lead_manager',
                'branch_id' => $kollengode?->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager.palakkad@kites.com'],
            [
                'name'      => 'Manager Palakkad',
                'password'  => Hash::make('password123'),
                'phone'     => '7777777702',
                'role'      => 'lead_manager',
                'branch_id' => $palakkad?->id,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager.thrissur@kites.com'],
            [
                'name'      => 'Manager Thrissur',
                'password'  => Hash::make('password123'),
                'phone'     => '7777777703',
                'role'      => 'lead_manager',
                'branch_id' => $thrissur?->id,
                'is_active' => true,
            ]
        );
    }
}
