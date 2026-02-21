<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the 3 branches immediately
        DB::table('branches')->insert([
            ['name' => 'Kollengode', 'code' => 'KLG', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Palakkad',   'code' => 'PKD', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Thrissur',   'code' => 'TCR', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
