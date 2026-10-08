<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->boolean('is_pre_lead')->default(false)->after('lead_code');
            $table->index('is_pre_lead');
        });
    }

    public function down(): void
    {
        Schema::table('edu_leads', function (Blueprint $table) {
            $table->dropIndex(['is_pre_lead']);
            $table->dropColumn('is_pre_lead');
        });
    }
};
