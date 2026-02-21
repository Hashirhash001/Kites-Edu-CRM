<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Migration
    public function up(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->string('preferred_state')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->dropColumn('preferred_state');
        });
    }
};
