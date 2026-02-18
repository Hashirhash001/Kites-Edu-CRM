<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $courses = [
            ['name' => 'MBA', 'country' => 'USA', 'is_active' => true],
            ['name' => 'MS in Computer Science', 'country' => 'USA', 'is_active' => true],
            ['name' => 'Bachelor in Engineering', 'country' => 'UK', 'is_active' => true],
            ['name' => 'Master in Data Science', 'country' => 'Canada', 'is_active' => true],
            ['name' => 'MBBS', 'country' => 'Germany', 'is_active' => true],
            ['name' => 'Diploma in Management', 'country' => 'Australia', 'is_active' => true],
            ['name' => 'Bachelor in Arts', 'country' => 'UK', 'is_active' => true],
            ['name' => 'MS in Artificial Intelligence', 'country' => 'USA', 'is_active' => true],
        ];

        foreach ($courses as $course) {
            DB::table('courses')->insert(array_merge($course, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    public function down(): void
    {
        DB::table('courses')->truncate();
    }
};
