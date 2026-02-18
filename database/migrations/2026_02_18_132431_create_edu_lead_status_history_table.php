<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_lead_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_lead_id')->constrained('edu_leads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->enum('old_interest_level', ['hot', 'warm', 'cold'])->nullable();
            $table->enum('new_interest_level', ['hot', 'warm', 'cold'])->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('edu_lead_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_lead_status_history');
    }
};
