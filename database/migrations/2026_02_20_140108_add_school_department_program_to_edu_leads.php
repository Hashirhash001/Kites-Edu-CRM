<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {

            // Institution type
            $table->enum('institution_type', ['school', 'college', 'other'])
                  ->nullable()->after('college');

            // School fields
            $table->string('school')->nullable()->after('institution_type');
            $table->string('school_department')->nullable()->after('school');

            // College department (college column already exists)
            $table->string('college_department')->nullable()->after('college');

            // branch_id (add only if not already present)
            if (!Schema::hasColumn('edu_leads', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('created_by');
            }

            // ── NO program_id FK here — programs table removed ────────
        });
    }

    public function down(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->dropColumn([
                'institution_type',
                'school',
                'school_department',
                'college_department',
            ]);

            if (Schema::hasColumn('edu_leads', 'branch_id')) {
                $table->dropColumn('branch_id');
            }
        });
    }
};
