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
        Schema::table('edu_leads', function (Blueprint $table) {
            // Remove old URL fields
            $table->dropColumn(['whatsapp_link', 'application_form_url']);

            // Add new status fields
            $table->string('whatsapp_status', 30)->default('not_sent')->after('booking_payment');
            $table->string('application_form_status', 30)->default('not_submitted')->after('whatsapp_status');
            $table->string('booking_status', 30)->default('not_paid')->after('application_form_status');
        });
    }

    public function down(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->string('whatsapp_link')->nullable();
            $table->string('application_form_url')->nullable();
            $table->dropColumn(['whatsapp_status', 'application_form_status', 'booking_status']);
        });
    }

};
