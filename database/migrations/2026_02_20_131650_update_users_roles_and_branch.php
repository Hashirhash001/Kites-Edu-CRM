<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update existing 'telecallers' → 'lead_manager' as fallback
        DB::statement("UPDATE users SET role = 'lead_manager' WHERE role = 'telecallers'");

        // Change enum to new roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'super_admin',
            'operation_head',
            'lead_manager'
        ) NOT NULL DEFAULT 'lead_manager'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM(
            'super_admin',
            'lead_manager',
            'telecallers'
        ) NOT NULL DEFAULT 'telecallers'");
    }
};
