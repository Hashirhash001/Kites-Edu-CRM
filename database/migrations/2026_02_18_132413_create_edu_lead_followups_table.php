<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_lead_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_lead_id')->constrained('edu_leads')->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->date('followup_date');
            $table->time('followup_time')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['followup_date', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('edu_lead_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_lead_followups');
    }
};
