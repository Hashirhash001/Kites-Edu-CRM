<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role`
            ENUM('super_admin','operation_head','lead_manager','telecaller')
            NOT NULL DEFAULT 'lead_manager'");
    }

    public function down(): void
    {
        // First move any telecallers to lead_manager before rolling back
        DB::statement("UPDATE `users` SET `role` = 'lead_manager' WHERE `role` = 'telecaller'");

        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role`
            ENUM('super_admin','operation_head','lead_manager')
            NOT NULL DEFAULT 'lead_manager'");
    }
};
