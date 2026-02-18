<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code')->unique(); // KITES-2026-0001

            // Foreign Keys
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('lead_source_id')->constrained('edu_lead_sources')->onDelete('cascade');
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('set null');

            // Student Basic Info
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('whatsapp_number')->nullable();
            $table->text('description')->nullable();

            // Education Specific
            $table->string('course_interested')->nullable();
            $table->string('country')->nullable();
            $table->string('college')->nullable();

            // Call Tracking
            $table->date('call_date')->nullable();
            $table->enum('call_status', ['connected', 'not_connected'])->nullable();
            $table->enum('interest_level', ['hot', 'warm', 'cold'])->nullable();

            // Follow-up
            $table->date('followup_date')->nullable();
            $table->enum('followup_status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->text('next_action')->nullable();

            // Final Status
            $table->enum('final_status', ['pending', 'admitted', 'not_interested'])->default('pending');
            $table->timestamp('admitted_at')->nullable();

            // Overall Status
            $table->enum('status', [
                'pending',
                'connected',
                'not_connected',
                'interested',
                'not_interested',
                'follow_up_scheduled',
                'admitted',
                'closed'
            ])->default('pending');

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('lead_code');
            $table->index('phone');
            $table->index('email');
            $table->index('interest_level');
            $table->index('final_status');
            $table->index('followup_date');
            $table->index('status');
            $table->index(['assigned_to', 'status']);
            $table->index(['followup_date', 'followup_status']);

            // Unique constraints per branch
            $table->unique(['phone'], 'edu_leads_phone_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_leads');
    }
};
