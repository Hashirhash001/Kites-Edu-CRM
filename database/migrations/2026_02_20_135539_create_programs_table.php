<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();        // Arts & Science, Engineering, etc.
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('programmes')->insert([
            ['name' => 'Arts & Science',        'description' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Engineering',            'description' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Allied & Health Science','description' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Nursing',                'description' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pharmacy',               'description' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medicine',               'description' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
