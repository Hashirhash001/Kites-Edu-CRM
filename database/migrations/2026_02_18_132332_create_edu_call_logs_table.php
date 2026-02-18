<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_lead_id')->constrained('edu_leads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('call_datetime');
            $table->enum('call_status', ['connected', 'not_connected']);
            $table->enum('interest_level', ['hot', 'warm', 'cold'])->nullable();
            $table->text('remarks')->nullable();
            $table->string('next_action')->nullable();
            $table->date('followup_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['edu_lead_id', 'call_datetime']);
            $table->index('user_id');
            $table->index('call_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_call_logs');
    }
};
