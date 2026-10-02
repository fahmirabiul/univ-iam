<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserProfile;
use App\Services\Academic\AcademicMasterDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicMasterDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private AcademicMasterDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AcademicMasterDataService();
    }

    public function test_generates_correct_nim_for_mahasiswa(): void
    {
        $nim1 = $this->service->generateNomorInduk('mahasiswa', 'Informatika', null, 2026);
        $this->assertSame('2611010001', $nim1);

        // Create profile with nim1 to simulate existing student
        $user = User::create(['email' => 'mhs1@univ.ac.id', 'password' => 'secret', 'is_active' => true]);
        UserProfile::create(['user_id' => $user->id, 'nama_lengkap' => 'Mhs 1', 'nomor_induk' => $nim1]);

        // Next student in same prodi should get sequence 0002
        $nim2 = $this->service->generateNomorInduk('mahasiswa', 'Informatika', null, 2026);
        $this->assertSame('2611010002', $nim2);
    }

    public function test_generates_correct_nidn_for_dosen(): void
    {
        $nidn1 = $this->service->generateNomorInduk('dosen', 'Informatika', null, 2026);
        $this->assertSame('202610110001', $nidn1);

        $user = User::create(['email' => 'dsn1@univ.ac.id', 'password' => 'secret', 'is_active' => true]);
        UserProfile::create(['user_id' => $user->id, 'nama_lengkap' => 'Dosen 1', 'nomor_induk' => $nidn1]);

        $nidn2 = $this->service->generateNomorInduk('dosen', 'Informatika', null, 2026);
        $this->assertSame('202610110002', $nidn2);
    }

    public function test_generates_correct_nipk_for_karyawan(): void
    {
        $nipk1 = $this->service->generateNomorInduk('karyawan', null, 'Biro SDM', 2026);
        $this->assertSame('20265010001', $nipk1);

        $user = User::create(['email' => 'staff1@univ.ac.id', 'password' => 'secret', 'is_active' => true]);
        UserProfile::create(['user_id' => $user->id, 'nama_lengkap' => 'Staff 1', 'nomor_induk' => $nipk1]);

        $nipk2 = $this->service->generateNomorInduk('karyawan', null, 'Biro SDM', 2026);
        $this->assertSame('20265010002', $nipk2);
    }

    public function test_resolves_faculty_by_program(): void
    {
        $faculty = $this->service->getFacultyByProgram('Informatika');
        $this->assertSame('Fakultas Teknik & Informatika', $faculty);

        $faculty2 = $this->service->getFacultyByProgram('Manajemen');
        $this->assertSame('Fakultas Ekonomi & Bisnis', $faculty2);
    }
}
