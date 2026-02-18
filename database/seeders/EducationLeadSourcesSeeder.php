<?php

namespace Database\Seeders;

use App\Models\EduLeadSource;
use Illuminate\Database\Seeder;

class EducationLeadSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['name' => 'Website'],
            ['name' => 'Referral'],
            ['name' => 'Social Media'],
            ['name' => 'Walk-in'],
            ['name' => 'Phone Call'],
            ['name' => 'Email'],
            ['name' => 'Education Fair'],
            ['name' => 'Agent/Partner'],
        ];

        foreach ($sources as $source) {
            EduLeadSource::updateOrCreate(
                ['name' => $source['name']],
                array_merge($source, ['is_active' => true])
            );
        }
    }
}
