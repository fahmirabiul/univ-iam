<?php

declare(strict_types=1);

namespace App\Services\Academic;

use App\Models\UserProfile;

class AcademicMasterDataService
{
    /**
     * Master Data Fakultas & Program Studi.
     *
     * @var array<string, array{name: string, programs: array<string, array{name: string, code: string, nim_code: string}>}>
     */
    public const FACULTIES = [
        'FTI' => [
            'name' => 'Fakultas Teknik & Informatika',
            'programs' => [
                'Informatika' => ['name' => 'Teknik Informatika', 'code' => '101', 'nim_code' => '1101'],
                'Sistem Informasi' => ['name' => 'Sistem Informasi', 'code' => '102', 'nim_code' => '1102'],
                'Teknik Elektro' => ['name' => 'Teknik Elektro', 'code' => '103', 'nim_code' => '1103'],
            ],
        ],
        'FEB' => [
            'name' => 'Fakultas Ekonomi & Bisnis',
            'programs' => [
                'Manajemen' => ['name' => 'Manajemen', 'code' => '201', 'nim_code' => '1201'],
                'Akuntansi' => ['name' => 'Akuntansi', 'code' => '202', 'nim_code' => '1202'],
            ],
        ],
        'FIKD' => [
            'name' => 'Fakultas Ilmu Komunikasi & Desain',
            'programs' => [
                'Ilmu Komunikasi' => ['name' => 'Ilmu Komunikasi', 'code' => '301', 'nim_code' => '1301'],
                'Desain Komunikasi Visual' => ['name' => 'Desain Komunikasi Visual', 'code' => '302', 'nim_code' => '1302'],
            ],
        ],
    ];

    /**
     * Master Data Unit Kerja (Khusus Karyawan & Admin).
     *
     * @var array<string, array{name: string, code: string}>
     */
    public const WORK_UNITS = [
        'Biro SDM' => ['name' => 'Biro Sumber Daya Manusia', 'code' => '501'],
        'Pusat TIK' => ['name' => 'Pusat Teknologi Informasi & Komunikasi', 'code' => '502'],
        'Biro Akademik' => ['name' => 'Biro Administrasi Akademik', 'code' => '503'],
        'Biro Keuangan' => ['name' => 'Biro Keuangan & Logistik', 'code' => '504'],
        'LPPM' => ['name' => 'Lembaga Penelitian & Pengabdian Masyarakat', 'code' => '505'],
        'Rektorat' => ['name' => 'Rektorat & Pimpinan Universitas', 'code' => '506'],
    ];

    /**
     * Opsi Status Keaktifan / Akademik per Peran.
     *
     * @var array<string, array<string, string>>
     */
    public const STATUS_OPTIONS = [
        'dosen' => [
            'aktif' => 'Aktif Mengajar',
            'studi_lanjut' => 'Tugas Belajar / Studi Lanjut',
            'cuti' => 'Cuti Akademik',
            'pensiun' => 'Pensiun / Purna Tugas',
            'keluar' => 'Resign / Pindah Instansi',
        ],
        'mahasiswa' => [
            'aktif' => 'Aktif Kuliah',
            'cuti' => 'Cuti Kuliah',
            'lulus' => 'Lulus / Alumni',
            'drop_out' => 'Drop Out',
            'non_aktif' => 'Non-Aktif',
        ],
        'karyawan' => [
            'aktif' => 'Aktif Bekerja',
            'cuti' => 'Cuti Kerja',
            'pensiun' => 'Pensiun / Purna Tugas',
            'resign' => 'Resign / Keluar',
            'non_aktif' => 'Non-Aktif',
        ],
        'admin_sdm' => [
            'aktif' => 'Aktif Bekerja',
            'cuti' => 'Cuti Kerja',
            'pensiun' => 'Pensiun / Purna Tugas',
            'resign' => 'Resign / Keluar',
            'non_aktif' => 'Non-Aktif',
        ],
        'super_admin' => [
            'aktif' => 'Aktif Bekerja',
            'cuti' => 'Cuti Kerja',
            'pensiun' => 'Pensiun / Purna Tugas',
            'resign' => 'Resign / Keluar',
            'non_aktif' => 'Non-Aktif',
        ],
    ];

    /**
     * Get list of all faculties.
     *
     * @return array<string, string>
     */
    public function getFaculties(): array
    {
        $result = [];
        foreach (self::FACULTIES as $code => $fac) {
            $result[$fac['name']] = $fac['name'];
        }

        return $result;
    }

    /**
     * Get list of study programs mapped to their faculties.
     *
     * @return array<string, array<string, string>>
     */
    public function getStudyProgramsGrouped(): array
    {
        $result = [];
        foreach (self::FACULTIES as $facultyCode => $faculty) {
            $programs = [];
            foreach ($faculty['programs'] as $key => $program) {
                $programs[$key] = $program['name'];
            }
            $result[$faculty['name']] = $programs;
        }

        return $result;
    }

    /**
     * Get flat list of all study program keys.
     *
     * @return list<string>
     */
    public function getStudyProgramKeys(): array
    {
        $keys = [];
        foreach (self::FACULTIES as $faculty) {
            foreach (array_keys($faculty['programs']) as $key) {
                $keys[] = (string) $key;
            }
        }

        return $keys;
    }

    /**
     * Find parent faculty name by study program key or name.
     */
    public function getFacultyByProgram(string $program): ?string
    {
        foreach (self::FACULTIES as $faculty) {
            if (isset($faculty['programs'][$program])) {
                return $faculty['name'];
            }
            foreach ($faculty['programs'] as $p) {
                if (strcasecmp($p['name'], $program) === 0) {
                    return $faculty['name'];
                }
            }
        }

        return null;
    }

    /**
     * Get list of work units.
     *
     * @return array<string, string>
     */
    public function getWorkUnits(): array
    {
        $result = [];
        foreach (self::WORK_UNITS as $key => $unit) {
            $result[$key] = $unit['name'];
        }

        return $result;
    }

    /**
     * Get valid status options for a specific role.
     *
     * @return array<string, string>
     */
    public function getStatusOptionsByRole(string $role): array
    {
        return self::STATUS_OPTIONS[$role] ?? self::STATUS_OPTIONS['dosen'];
    }

    /**
     * Generate a unique and sequential identification number (NIM, NIDN/NIP, NIPK).
     */
    public function generateNomorInduk(
        string $role,
        ?string $programStudi = null,
        ?string $unitKerja = null,
        ?int $year = null,
    ): string {
        $year = $year ?? (int) date('Y');

        if ($role === 'mahasiswa') {
            // NIM format: [Year 2 digits][Prodi NIM Code 4 digits][Sequence 4 digits] -> Example: 2611010001
            $nimCode = $this->resolveProdiNimCode($programStudi);
            $prefix = sprintf('%s%s', substr((string) $year, -2), $nimCode);

            return $this->generateSequentialNumber($prefix, 4);
        }

        if ($role === 'dosen') {
            // NIDN/NIP Dosen format: [Year 4 digits][Prodi Code 3 digits][Type 1 digit = 1][Sequence 4 digits] -> Example: 202610110001
            $prodiCode = $this->resolveProdiCode($programStudi);
            $prefix = sprintf('%d%s1', $year, $prodiCode);

            return $this->generateSequentialNumber($prefix, 4);
        }

        // Karyawan / Admin format: [Year 4 digits][Unit Code 3 digits][Sequence 4 digits] -> Example: 20265010001
        $unitCode = $this->resolveUnitCode($unitKerja);
        $prefix = sprintf('%d%s', $year, $unitCode);

        return $this->generateSequentialNumber($prefix, 4);
    }

    private function resolveProdiNimCode(?string $program): string
    {
        if (! $program) {
            return '1101';
        }

        foreach (self::FACULTIES as $faculty) {
            if (isset($faculty['programs'][$program])) {
                return $faculty['programs'][$program]['nim_code'];
            }
            foreach ($faculty['programs'] as $p) {
                if (strcasecmp($p['name'], $program) === 0) {
                    return $p['nim_code'];
                }
            }
        }

        return '1101';
    }

    private function resolveProdiCode(?string $program): string
    {
        if (! $program) {
            return '101';
        }

        foreach (self::FACULTIES as $faculty) {
            if (isset($faculty['programs'][$program])) {
                return $faculty['programs'][$program]['code'];
            }
            foreach ($faculty['programs'] as $p) {
                if (strcasecmp($p['name'], $program) === 0) {
                    return $p['code'];
                }
            }
        }

        return '101';
    }

    private function resolveUnitCode(?string $unit): string
    {
        if (! $unit) {
            return '501';
        }

        if (isset(self::WORK_UNITS[$unit])) {
            return self::WORK_UNITS[$unit]['code'];
        }

        foreach (self::WORK_UNITS as $u) {
            if (strcasecmp($u['name'], $unit) === 0) {
                return $u['code'];
            }
        }

        return '501';
    }

    /**
     * Generate the next available sequential number for a given prefix.
     */
    private function generateSequentialNumber(string $prefix, int $sequenceLength = 4): string
    {
        // Query latest matching nomor_induk
        $latestRecord = UserProfile::where('nomor_induk', 'like', "{$prefix}%")
            ->orderByDesc('nomor_induk')
            ->value('nomor_induk');

        $nextSequence = 1;

        if ($latestRecord && strlen($latestRecord) >= strlen($prefix) + $sequenceLength) {
            $extractedSeq = (int) substr($latestRecord, strlen($prefix), $sequenceLength);
            $nextSequence = $extractedSeq + 1;
        }

        return sprintf('%s%0' . $sequenceLength . 'd', $prefix, $nextSequence);
    }
}
