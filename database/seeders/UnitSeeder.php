<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Unit::create([
            'name' => 'Emergency Room',
            'code' => 'ER',
            'description' => 'Emergency and trauma care unit'
        ]);

        \App\Models\Unit::create([
            'name' => 'ICU',
            'code' => 'ICU',
            'description' => 'Intensive Care Unit'
        ]);

        \App\Models\Unit::create([
            'name' => 'General Surgery',
            'code' => 'SUR',
            'description' => 'General surgery department'
        ]);

        \App\Models\Unit::create([
            'name' => 'Internal Medicine',
            'code' => 'MED',
            'description' => 'Internal medicine department'
        ]);

        \App\Models\Unit::create([
            'name' => 'Pediatrics',
            'code' => 'PED',
            'description' => 'Children\'s healthcare unit'
        ]);

        \App\Models\Unit::create([
            'name' => 'Obstetrics & Gynecology',
            'code' => 'OBG',
            'description' => 'Women\'s health and maternity unit'
        ]);

        \App\Models\Unit::create([
            'name' => 'Radiology',
            'code' => 'RAD',
            'description' => 'Diagnostic imaging services'
        ]);

        \App\Models\Unit::create([
            'name' => 'Laboratory',
            'code' => 'LAB',
            'description' => 'Clinical laboratory services'
        ]);

        \App\Models\Unit::create([
            'name' => 'Pharmacy',
            'code' => 'PHR',
            'description' => 'Hospital pharmacy services'
        ]);

        \App\Models\Unit::create([
            'name' => 'Physical Therapy',
            'code' => 'PTH',
            'description' => 'Rehabilitation and physical therapy services'
        ]);
    }
}
