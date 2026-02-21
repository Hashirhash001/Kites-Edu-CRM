<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Add telecaller role support to users ──────────────────────
        // No schema change needed — role is already a string column
        // Just add branch_id nullable for telecallers (already exists)

        // ── New EduLead fields ────────────────────────────────────────
        Schema::table('edu_leads', function (Blueprint $table) {
            // Agent / Telecaller who sourced the lead
            $table->string('agent_name')->nullable()->after('created_by');

            // Location fields
            $table->string('state')->nullable()->after('country');
            $table->string('district')->nullable()->after('state');

            // Rename 'country' semantically to 'preferred_country' — no rename needed,
            // just treat it as preferred country in the UI

            // Addon course (secondary course interest)
            $table->string('addon_course')->nullable()->after('course_interested');

            // Application & payment tracking (static fields)
            $table->string('application_number')->nullable()->after('addon_course');
            $table->string('whatsapp_link')->nullable()->after('application_number');
            $table->string('application_form_url')->nullable()->after('whatsapp_link');
            $table->decimal('booking_payment', 10, 2)->nullable()->after('application_form_url');
            $table->decimal('fees_collection', 10, 2)->nullable()->after('booking_payment');
            $table->text('cancellation_reason')->nullable()->after('fees_collection');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->dropColumn([
                'agent_name', 'state', 'district', 'addon_course',
                'application_number', 'whatsapp_link', 'application_form_url',
                'booking_payment', 'fees_collection',
                'cancellation_reason', 'cancelled_at',
            ]);
        });
    }
};
