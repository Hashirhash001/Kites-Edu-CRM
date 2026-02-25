<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE edu_leads MODIFY COLUMN final_status ENUM(
            'pending',
            'contacted',
            'not_interested',
            'follow_up',
            'admitted',
            'dropped',
            'not_attended'
        ) DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE edu_leads MODIFY COLUMN final_status ENUM(
            'pending',
            'contacted',
            'not_interested',
            'follow_up',
            'admitted',
            'dropped'
        ) DEFAULT 'pending'");
    }

};
