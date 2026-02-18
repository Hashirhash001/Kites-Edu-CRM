<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edu_lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edu_lead_id')->constrained('edu_leads')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('note');
            $table->timestamps();
            $table->softDeletes();

            $table->index('edu_lead_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edu_lead_notes');
    }
};
