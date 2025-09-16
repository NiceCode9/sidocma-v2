<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get units for assignment
        $units = Unit::all();
        $erUnit = $units->where('code', 'ER')->first();
        $icuUnit = $units->where('code', 'ICU')->first();
        $surgeryUnit = $units->where('code', 'SUR')->first();
        $medUnit = $units->where('code', 'MED')->first();
        $pedUnit = $units->where('code', 'PED')->first();
        $obgUnit = $units->where('code', 'OBG')->first();
        $radUnit = $units->where('code', 'RAD')->first();
        $labUnit = $units->where('code', 'LAB')->first();
        $pharUnit = $units->where('code', 'PHR')->first();
        $pthUnit = $units->where('code', 'PTH')->first();

        // Create Direktur
        $direktur = User::create([
            'name' => 'Dr. Ahmad Sutrisno',
            'username' => 'direktur',
            'email' => 'direktur@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'DIR001',
            'unit_id' => null, // Direktur tidak terikat pada unit tertentu
            'is_active' => true,
        ]);
        $direktur->assignRole('direktur');

        // Create Kepala Bagian (Kabag) for each major unit
        $kabagER = User::create([
            'name' => 'Dr. Siti Rahayu',
            'username' => 'kabag.er',
            'email' => 'kabag.er@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBG001',
            'unit_id' => $erUnit->id,
            'is_active' => true,
        ]);
        $kabagER->assignRole('kabag');

        $kabagICU = User::create([
            'name' => 'Dr. Budi Santoso',
            'username' => 'kabag.icu',
            'email' => 'kabag.icu@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBG002',
            'unit_id' => $icuUnit->id,
            'is_active' => true,
        ]);
        $kabagICU->assignRole('kabag');

        $kabagSurgery = User::create([
            'name' => 'Dr. Indira Permata',
            'username' => 'kabag.surgery',
            'email' => 'kabag.surgery@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBG003',
            'unit_id' => $surgeryUnit->id,
            'is_active' => true,
        ]);
        $kabagSurgery->assignRole('kabag');

        $kabagMed = User::create([
            'name' => 'Dr. Wijaya Kusuma',
            'username' => 'kabag.med',
            'email' => 'kabag.med@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBG004',
            'unit_id' => $medUnit->id,
            'is_active' => true,
        ]);
        $kabagMed->assignRole('kabag');

        // Create Kepala Bidang (Kabid)
        $kabidPed = User::create([
            'name' => 'Dr. Maya Sari',
            'username' => 'kabid.ped',
            'email' => 'kabid.ped@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBD001',
            'unit_id' => $pedUnit->id,
            'is_active' => true,
        ]);
        $kabidPed->assignRole('kasi');

        $kabidOBG = User::create([
            'name' => 'Dr. Retno Wulandari',
            'username' => 'kabid.obg',
            'email' => 'kabid.obg@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBD002',
            'unit_id' => $obgUnit->id,
            'is_active' => true,
        ]);
        $kabidOBG->assignRole('kasi');

        $kabidRad = User::create([
            'name' => 'Dr. Hendro Prasetyo',
            'username' => 'kabid.rad',
            'email' => 'kabid.rad@hospital.com',
            'password' => bcrypt('password'),
            'kode_user' => 'KBD003',
            'unit_id' => $radUnit->id,
            'is_active' => true,
        ]);
        $kabidRad->assignRole('kasi');

        // Create Staff for various units
        $staffUsers = [
            [
                'name' => 'Ns. Ani Susanti',
                'username' => 'staff.er1',
                'email' => 'ani.susanti@hospital.com',
                'kode_user' => 'STF001',
                'unit_id' => $erUnit->id,
            ],
            [
                'name' => 'Ns. Dedi Kurniawan',
                'username' => 'staff.er2',
                'email' => 'dedi.kurniawan@hospital.com',
                'kode_user' => 'STF002',
                'unit_id' => $erUnit->id,
            ],
            [
                'name' => 'Ns. Rina Marlina',
                'username' => 'staff.icu1',
                'email' => 'rina.marlina@hospital.com',
                'kode_user' => 'STF003',
                'unit_id' => $icuUnit->id,
            ],
            [
                'name' => 'Ns. Bambang Sutedjo',
                'username' => 'staff.icu2',
                'email' => 'bambang.sutedjo@hospital.com',
                'kode_user' => 'STF004',
                'unit_id' => $icuUnit->id,
            ],
            [
                'name' => 'Dr. Putri Andini',
                'username' => 'staff.surgery1',
                'email' => 'putri.andini@hospital.com',
                'kode_user' => 'STF005',
                'unit_id' => $surgeryUnit->id,
            ],
            [
                'name' => 'Ns. Agus Setiawan',
                'username' => 'staff.surgery2',
                'email' => 'agus.setiawan@hospital.com',
                'kode_user' => 'STF006',
                'unit_id' => $surgeryUnit->id,
            ],
            [
                'name' => 'Dr. Lestari Wati',
                'username' => 'staff.med1',
                'email' => 'lestari.wati@hospital.com',
                'kode_user' => 'STF007',
                'unit_id' => $medUnit->id,
            ],
            [
                'name' => 'Ns. Eko Prasetyo',
                'username' => 'staff.med2',
                'email' => 'eko.prasetyo@hospital.com',
                'kode_user' => 'STF008',
                'unit_id' => $medUnit->id,
            ],
            [
                'name' => 'Dr. Fitria Sari',
                'username' => 'staff.ped1',
                'email' => 'fitria.sari@hospital.com',
                'kode_user' => 'STF009',
                'unit_id' => $pedUnit->id,
            ],
            [
                'name' => 'Ns. Dwi Cahyono',
                'username' => 'staff.ped2',
                'email' => 'dwi.cahyono@hospital.com',
                'kode_user' => 'STF010',
                'unit_id' => $pedUnit->id,
            ],
            [
                'name' => 'Bd. Yuni Astuti',
                'username' => 'staff.obg1',
                'email' => 'yuni.astuti@hospital.com',
                'kode_user' => 'STF011',
                'unit_id' => $obgUnit->id,
            ],
            [
                'name' => 'Ns. Rudi Hermawan',
                'username' => 'staff.obg2',
                'email' => 'rudi.hermawan@hospital.com',
                'kode_user' => 'STF012',
                'unit_id' => $obgUnit->id,
            ],
            [
                'name' => 'Radiografer Sinta Dewi',
                'username' => 'staff.rad1',
                'email' => 'sinta.dewi@hospital.com',
                'kode_user' => 'STF013',
                'unit_id' => $radUnit->id,
            ],
            [
                'name' => 'Radiografer Andi Wijaya',
                'username' => 'staff.rad2',
                'email' => 'andi.wijaya@hospital.com',
                'kode_user' => 'STF014',
                'unit_id' => $radUnit->id,
            ],
            [
                'name' => 'Analis Kesehatan Sri Wahyuni',
                'username' => 'staff.lab1',
                'email' => 'sri.wahyuni@hospital.com',
                'kode_user' => 'STF015',
                'unit_id' => $labUnit->id,
            ],
            [
                'name' => 'Analis Kesehatan Hendra Gunawan',
                'username' => 'staff.lab2',
                'email' => 'hendra.gunawan@hospital.com',
                'kode_user' => 'STF016',
                'unit_id' => $labUnit->id,
            ],
            [
                'name' => 'Apoteker Dewi Sartika',
                'username' => 'staff.phar1',
                'email' => 'dewi.sartika@hospital.com',
                'kode_user' => 'STF017',
                'unit_id' => $pharUnit->id,
            ],
            [
                'name' => 'TTK Pharmacy Joko Susilo',
                'username' => 'staff.phar2',
                'email' => 'joko.susilo@hospital.com',
                'kode_user' => 'STF018',
                'unit_id' => $pharUnit->id,
            ],
            [
                'name' => 'Fisioterapis Nova Lestari',
                'username' => 'staff.pth1',
                'email' => 'nova.lestari@hospital.com',
                'kode_user' => 'STF019',
                'unit_id' => $pthUnit->id,
            ],
            [
                'name' => 'Fisioterapis Bayu Aditya',
                'username' => 'staff.pth2',
                'email' => 'bayu.aditya@hospital.com',
                'kode_user' => 'STF020',
                'unit_id' => $pthUnit->id,
            ],
        ];

        foreach ($staffUsers as $staffData) {
            $staff = User::create([
                'name' => $staffData['name'],
                'username' => $staffData['username'],
                'email' => $staffData['email'],
                'password' => bcrypt('password'),
                'kode_user' => $staffData['kode_user'],
                'unit_id' => $staffData['unit_id'],
                'is_active' => true,
            ]);
            $staff->assignRole('staff');
        }
    }
}
