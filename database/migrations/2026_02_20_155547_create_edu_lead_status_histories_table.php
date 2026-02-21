<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_lead_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_lead_id')
                  ->constrained('edu_leads')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->string('old_interest_level')->nullable();
            $table->string('new_interest_level')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_lead_status_histories');
    }
};
