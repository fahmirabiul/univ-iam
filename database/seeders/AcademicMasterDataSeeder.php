<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\WorkUnit;
use Illuminate\Database\Seeder;

class AcademicMasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds for Faculties, Study Programs, and Work Units.
     */
    public function run(): void
    {
        // 1. Fakultas & Program Studi
        $facultiesData = [
            [
                'code' => 'FT',
                'name' => 'Fakultas Teknik',
                'programs' => [
                    ['code' => '101', 'nim_code' => '1101', 'name' => 'Teknik Informatika'],
                    ['code' => '102', 'nim_code' => '1102', 'name' => 'Teknik Elektro'],
                ],
            ],
            [
                'code' => 'FEB',
                'name' => 'Fakultas Ekonomi & Bisnis',
                'programs' => [
                    ['code' => '201', 'nim_code' => '1201', 'name' => 'Manajemen'],
                    ['code' => '202', 'nim_code' => '1202', 'name' => 'Akuntansi'],
                ],
            ],
            [
                'code' => 'FSRD',
                'name' => 'Fakultas Seni Rupa dan Desain',
                'programs' => [
                    ['code' => '301', 'nim_code' => '1301', 'name' => 'Desain Interior'],
                    ['code' => '302', 'nim_code' => '1302', 'name' => 'Desain Komunikasi Visual'],
                ],
            ],
        ];

        foreach ($facultiesData as $fData) {
            $faculty = Faculty::updateOrCreate(
                ['code' => $fData['code']],
                ['name' => $fData['name']]
            );

            foreach ($fData['programs'] as $pData) {
                StudyProgram::updateOrCreate(
                    ['code' => $pData['code']],
                    [
                        'faculty_id' => $faculty->id,
                        'nim_code' => $pData['nim_code'],
                        'name' => $pData['name'],
                    ]
                );
            }
        }

        // 2. Unit Kerja
        $workUnitsData = [
            ['code' => '501', 'name' => 'UPT TIK'],
            ['code' => '502', 'name' => 'Biro SDM'],
            ['code' => '503', 'name' => 'LPPM'],
        ];

        foreach ($workUnitsData as $uData) {
            WorkUnit::updateOrCreate(
                ['code' => $uData['code']],
                ['name' => $uData['name']]
            );
        }
    }
}
