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
            $table->string('referral_name')->nullable()->after('agent_name');
        });
    }

    public function down(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->dropColumn('referral_name');
        });
    }

};
