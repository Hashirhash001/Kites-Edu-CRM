<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (!Schema::hasColumn('courses', 'programme_id')) {
                $table->unsignedBigInteger('programme_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('courses', 'duration')) {
                $table->string('duration')->nullable()->after('name');
            }
            if (!Schema::hasColumn('courses', 'description')) {
                $table->text('description')->nullable()->after('duration');
            }
            if (Schema::hasColumn('courses', 'category')) {
                $table->dropColumn('category');
            }
        });

        // ── Disable FK checks so we can clear old placeholder data ────
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('courses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ── Fetch programme IDs ───────────────────────────────────────
        $p = DB::table('programmes')->pluck('id', 'name');

        $courses = [

            // ── ARTS & SCIENCE ────────────────────────────────────────
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Artificial Intelligence and Machine Learning',   'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Digital and Cyber Forensic Science',            'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Computer Science with Data Analytics',          'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BCA (AI + Cloud Computing)',                         'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BCA (AI + Cyber Security)',                          'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BCA Artificial Intelligence',                        'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BCA AR & VR',                                        'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Computer Science',                              'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Forensic Science',                              'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Com CA (Aviation + Logistics)',                    'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BBA CA (Aviation + Logistics)',                      'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BBA Aviation',                                       'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BBA Logistics and Supply Chain Management',          'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Catering Science and Hotel Management',         'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Costume Design and Fashion',                    'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Sc Biotechnology',                                 'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BCA Cyber Security with Data Science',               'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BBA Aviation with Hospitality Management',           'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'BBA Logistics with Finance',                         'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'B.Com FinTech',                                      'duration' => '3 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'MBA',                                                'duration' => '2 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'M.Com',                                              'duration' => '2 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'M.Sc Cyber Security',                                'duration' => '2 years'],
            ['programme_id' => $p['Arts & Science'], 'name' => 'M.Sc Computer Science',                              'duration' => '2 years'],

            // ── ENGINEERING ───────────────────────────────────────────
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Artificial Intelligence & Machine Learning',     'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Cybersecurity Engineering',                      'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Computer Science Engineering',                   'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Artificial Intelligence and Data Science',       'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Electrical and Electronics Engineering',         'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Electronics and Communication Engineering',      'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Biotechnology and Biochemical Engineering',      'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Food Technology Engineering',                    'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Agricultural Engineering',                       'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Mechanical Engineering',                         'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Robotics Engineering',                           'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Genetic Engineering',                            'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Mechatronics Engineering',                       'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Aerospace Engineering',                          'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Aeronautical Engineering',                       'duration' => '4 years'],
            ['programme_id' => $p['Engineering'], 'name' => 'B.Tech Chemical Engineering',                           'duration' => '4 years'],

            // ── ALLIED & HEALTH SCIENCE ───────────────────────────────
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Radiology & Imaging Technology',               'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Cardiac Technology',                           'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Dialysis Technology',                          'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Physician Assistant',                          'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Operation Theatre and Anaesthesia Technology',  'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Accident and Emergency Care Technology',        'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Respiratory Therapy',                          'duration' => '3 years'],
            ['programme_id' => $p['Allied & Health Science'], 'name' => 'B.Sc Medical Laboratory Technology',                'duration' => '3 years'],

            // ── NURSING ───────────────────────────────────────────────
            ['programme_id' => $p['Nursing'], 'name' => 'B.Sc Nursing',                       'duration' => '4 years'],
            ['programme_id' => $p['Nursing'], 'name' => 'GNM (General Nursing and Midwifery)', 'duration' => '3 years'],

            // ── PHARMACY ─────────────────────────────────────────────
            ['programme_id' => $p['Pharmacy'], 'name' => 'B.Pharm', 'duration' => '4 years'],
            ['programme_id' => $p['Pharmacy'], 'name' => 'D.Pharm', 'duration' => '2 years'],
            ['programme_id' => $p['Pharmacy'], 'name' => 'Pharm.D', 'duration' => '6 years'],

            // ── MEDICINE ─────────────────────────────────────────────
            ['programme_id' => $p['Medicine'], 'name' => 'MBBS', 'duration' => '5.5 years'],
        ];

        foreach ($courses as $course) {
            DB::table('courses')->insert(array_merge($course, [
                'country'    => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // ── Add FK after data is inserted ─────────────────────────────
        Schema::table('courses', function (Blueprint $table) {
            $table->foreign('programme_id')
                  ->references('id')->on('programmes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['programme_id']);
            $table->dropColumn(['programme_id', 'duration', 'description']);
        });

        // Re-add category column on rollback
        Schema::table('courses', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });
    }
};
