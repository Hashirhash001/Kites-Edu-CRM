<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Expand enum to include ALL old + new values so no row is invalid
        DB::statement("
            ALTER TABLE edu_leads
            MODIFY COLUMN `status`
            ENUM(
                'whatsapp_link_submitted',
                'application_form_submitted',
                'booking',
                'cancelled',
                'pending',
                'connected',
                'not_connected',
                'interested',
                'not_interested',
                'follow_up_scheduled',
                'admitted',
                'closed'
            )
            NOT NULL DEFAULT 'pending'
        ");

        // Step 2: Map old status values that have a direct equivalent, rest → 'pending' (temp placeholder)
        DB::statement("
            UPDATE edu_leads
            SET status = CASE
                WHEN status = 'admitted'   THEN 'booking'
                WHEN status IN (
                    'pending','connected','not_connected',
                    'interested','not_interested',
                    'follow_up_scheduled','closed'
                ) THEN 'whatsapp_link_submitted'
                ELSE status
            END
        ");

        // Step 3: Now all rows only have new-enum values — drop the old ones and set nullable
        DB::statement("
            ALTER TABLE edu_leads
            MODIFY COLUMN `status`
            ENUM('whatsapp_link_submitted','application_form_submitted','booking','cancelled')
            NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        // Step 1: Expand to all values
        DB::statement("
            ALTER TABLE edu_leads
            MODIFY COLUMN `status`
            ENUM(
                'whatsapp_link_submitted',
                'application_form_submitted',
                'booking',
                'cancelled',
                'pending',
                'connected',
                'not_connected',
                'interested',
                'not_interested',
                'follow_up_scheduled',
                'admitted',
                'closed'
            )
            NULL DEFAULT NULL
        ");

        // Step 2: Map back to original values
        DB::statement("
            UPDATE edu_leads
            SET status = CASE
                WHEN status = 'booking'                    THEN 'admitted'
                WHEN status = 'whatsapp_link_submitted'    THEN 'pending'
                WHEN status = 'application_form_submitted' THEN 'pending'
                WHEN status = 'cancelled'                  THEN 'closed'
                ELSE 'pending'
            END
        ");

        // Step 3: Restore original enum with NOT NULL
        DB::statement("
            ALTER TABLE edu_leads
            MODIFY COLUMN `status`
            ENUM('pending','connected','not_connected','interested','not_interested','follow_up_scheduled','admitted','closed')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
