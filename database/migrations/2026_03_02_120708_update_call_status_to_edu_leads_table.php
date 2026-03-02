<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE edu_leads
            MODIFY COLUMN call_status
            ENUM('connected', 'not_connected', 'contacted', 'not_attended') NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE edu_leads
            MODIFY COLUMN call_status
            ENUM('connected', 'not_connected') NULL
        ");
    }
};
