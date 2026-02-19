<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ALTER the enum to include all statuses
        DB::statement("ALTER TABLE edu_leads MODIFY COLUMN final_status
            ENUM('pending','contacted','not_interested','follow_up','admitted','dropped')
            NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE edu_leads MODIFY COLUMN final_status
            ENUM('pending','admitted','not_interested')
            NOT NULL DEFAULT 'pending'");
    }
};
