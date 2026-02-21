<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First ensure the courses table has all needed columns
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'category')) {
                $table->string('category')->nullable()->after('name');
                // e.g. Medical, Engineering, Management, Science
            }
            if (!Schema::hasColumn('courses', 'duration')) {
                $table->string('duration')->nullable()->after('country');
                // e.g. "4 years", "2 years"
            }
            if (!Schema::hasColumn('courses', 'description')) {
                $table->text('description')->nullable()->after('duration');
            }
        });

        $courses = [
            // ── Medical ──────────────────────────────────────────────
            ['name' => 'MBBS',                      'category' => 'Medical',      'country' => 'Russia',      'duration' => '6 years'],
            ['name' => 'MBBS',                      'category' => 'Medical',      'country' => 'China',       'duration' => '6 years'],
            ['name' => 'MBBS',                      'category' => 'Medical',      'country' => 'Kazakhstan',  'duration' => '5 years'],
            ['name' => 'MBBS',                      'category' => 'Medical',      'country' => 'Georgia',     'duration' => '6 years'],
            ['name' => 'MBBS',                      'category' => 'Medical',      'country' => 'Philippines', 'duration' => '5.5 years'],
            ['name' => 'MBBS',                      'category' => 'Medical',      'country' => 'Germany',     'duration' => '6 years'],
            ['name' => 'BDS',                       'category' => 'Medical',      'country' => 'Russia',      'duration' => '5 years'],
            ['name' => 'BSc Nursing',               'category' => 'Medical',      'country' => 'UK',          'duration' => '3 years'],
            ['name' => 'MD / MS (Postgraduate)',    'category' => 'Medical',      'country' => 'Russia',      'duration' => '3 years'],

            // ── Engineering ──────────────────────────────────────────
            ['name' => 'Bachelor in Engineering',   'category' => 'Engineering',  'country' => 'UK',          'duration' => '3 years'],
            ['name' => 'B.Tech',                    'category' => 'Engineering',  'country' => 'Germany',     'duration' => '4 years'],
            ['name' => 'MS in Computer Science',    'category' => 'Engineering',  'country' => 'USA',         'duration' => '2 years'],
            ['name' => 'MS in Artificial Intelligence', 'category' => 'Engineering', 'country' => 'USA',      'duration' => '2 years'],
            ['name' => 'MS in Data Science',        'category' => 'Engineering',  'country' => 'Canada',      'duration' => '2 years'],
            ['name' => 'Master in Data Science',    'category' => 'Engineering',  'country' => 'Canada',      'duration' => '2 years'],
            ['name' => 'BE / B.Tech',               'category' => 'Engineering',  'country' => 'Australia',   'duration' => '4 years'],

            // ── Management ───────────────────────────────────────────
            ['name' => 'MBA',                       'category' => 'Management',   'country' => 'USA',         'duration' => '2 years'],
            ['name' => 'MBA',                       'category' => 'Management',   'country' => 'UK',          'duration' => '1 year'],
            ['name' => 'MBA',                       'category' => 'Management',   'country' => 'Canada',      'duration' => '2 years'],
            ['name' => 'MBA',                       'category' => 'Management',   'country' => 'Australia',   'duration' => '2 years'],
            ['name' => 'BBA',                       'category' => 'Management',   'country' => 'UK',          'duration' => '3 years'],
            ['name' => 'Diploma in Management',     'category' => 'Management',   'country' => 'Australia',   'duration' => '1 year'],

            // ── Arts & Humanities ────────────────────────────────────
            ['name' => 'Bachelor in Arts',          'category' => 'Arts',         'country' => 'UK',          'duration' => '3 years'],
            ['name' => 'Bachelor in Arts',          'category' => 'Arts',         'country' => 'Canada',      'duration' => '4 years'],

            // ── Science ──────────────────────────────────────────────
            ['name' => 'BSc',                       'category' => 'Science',      'country' => 'UK',          'duration' => '3 years'],
            ['name' => 'MSc',                       'category' => 'Science',      'country' => 'Germany',     'duration' => '2 years'],
        ];

        foreach ($courses as $course) {
            // Avoid duplicates on re-run
            $exists = DB::table('courses')
                ->where('name', $course['name'])
                ->where('country', $course['country'])
                ->exists();

            if (!$exists) {
                DB::table('courses')->insert(array_merge($course, [
                    'is_active'  => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('courses')->truncate();
    }
};
