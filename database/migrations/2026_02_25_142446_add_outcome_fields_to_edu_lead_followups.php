<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add outcome + followup_number to followups ─────────────────
        Schema::table('edu_lead_followups', function (Blueprint $table) {
            $table->unsignedTinyInteger('followup_number')
                  ->nullable()
                  ->after('edu_lead_id')
                  ->comment('1st, 2nd, 3rd… auto-set on creation');

            $table->string('outcome_final_status')->nullable()
                  ->after('status')
                  ->comment('Lead final_status recorded at completion');

            $table->string('outcome_status')->nullable()
                  ->after('outcome_final_status')
                  ->comment('Lead status (next-action) recorded at completion');

            $table->string('outcome_interest')->nullable()
                  ->after('outcome_status')
                  ->comment('hot / warm / cold at time of completion');

            $table->text('outcome_notes')->nullable()
                  ->after('outcome_interest')
                  ->comment('What happened during this followup');

            $table->string('next_action')->nullable()
                  ->after('outcome_notes')
                  ->comment('What the telecaller plans to do next');
        });

        // ── 2. Add followup_id to status history so every status change
        //       can be traced back to the followup that caused it ──────────
        Schema::table('edu_lead_status_histories', function (Blueprint $table) {
            $table->foreignId('followup_id')
                  ->nullable()
                  ->after('user_id')
                  ->constrained('edu_lead_followups')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('edu_lead_followups', function (Blueprint $table) {
            $table->dropColumn([
                'followup_number',
                'outcome_final_status',
                'outcome_status',
                'outcome_interest',
                'outcome_notes',
                'next_action',
            ]);
        });

        Schema::table('edu_lead_status_histories', function (Blueprint $table) {
            $table->dropForeign(['followup_id']);
            $table->dropColumn('followup_id');
        });
    }
};
