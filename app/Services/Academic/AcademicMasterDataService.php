<?php

declare(strict_types=1);

namespace App\Services\Academic;

use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\UserProfile;
use App\Models\WorkUnit;

class AcademicMasterDataService
{
    /**
     * Get list of all faculties.
     *
     * @return array<int, string>
     */
    public function getFaculties(): array
    {
        return Faculty::orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Get list of study programs mapped to their faculties.
     *
     * @return array<string, array<int, string>>
     */
    public function getStudyProgramsGrouped(): array
    {
        $faculties = Faculty::with(['studyPrograms' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();
        $result = [];

        foreach ($faculties as $faculty) {
            $programs = [];
            foreach ($faculty->studyPrograms as $program) {
                $programs[$program->id] = $program->name;
            }
            $result[$faculty->name] = $programs;
        }

        return $result;
    }

    /**
     * Get list of work units.
     *
     * @return array<int, string>
     */
    public function getWorkUnits(): array
    {
        return WorkUnit::orderBy('name')->pluck('name', 'id')->all();
    }

    /**
     * Get faculty name for a given study program.
     */
    public function getFacultyByProgram(int|string $identifier): ?string
    {
        $program = is_numeric($identifier)
            ? StudyProgram::with('faculty')->find($identifier)
            : StudyProgram::where('code', $identifier)
                ->orWhere('name', $identifier)
                ->with('faculty')
                ->first();

        return $program?->faculty?->name;
    }

    /**
     * Generate a unique and sequential identification number (NIM, NIDN/NIP, NIPK).
     */
    public function generateNomorInduk(
        string $role,
        int|string|null $studyProgram = null,
        int|string|null $workUnit = null,
        ?int $year = null,
    ): string {
        $year = $year ?? (int) date('Y');

        if ($role === 'mahasiswa') {
            // NIM format: [Year 2 digits][Prodi NIM Code 4 digits][Sequence 4 digits] -> Example: 2611010001
            $nimCode = $this->resolveProdiNimCode($studyProgram);
            $prefix = sprintf('%s%s', substr((string) $year, -2), $nimCode);

            return $this->generateSequentialNumber($prefix, 4);
        }

        if ($role === 'dosen') {
            // NIDN/NIP Dosen format: [Year 4 digits][Prodi Code 3 digits][Type 1 digit = 1][Sequence 4 digits] -> Example: 202610110001
            $prodiCode = $this->resolveProdiCode($studyProgram);
            $prefix = sprintf('%d%s1', $year, $prodiCode);

            return $this->generateSequentialNumber($prefix, 4);
        }

        // Karyawan / Admin format: [Year 4 digits][Unit Code 3 digits][Sequence 4 digits] -> Example: 20265010001
        $unitCode = $this->resolveUnitCode($workUnit);
        $prefix = sprintf('%d%s', $year, $unitCode);

        return $this->generateSequentialNumber($prefix, 4);
    }

    public function resolveProdiNimCode(int|string|null $identifier): string
    {
        if (empty($identifier)) {
            return '1101';
        }

        $program = is_numeric($identifier)
            ? StudyProgram::find($identifier)
            : StudyProgram::where('code', $identifier)
                ->orWhere('name', $identifier)
                ->first();

        return $program?->nim_code ?? '1101';
    }

    public function resolveProdiCode(int|string|null $identifier): string
    {
        if (empty($identifier)) {
            return '101';
        }

        $program = is_numeric($identifier)
            ? StudyProgram::find($identifier)
            : StudyProgram::where('code', $identifier)
                ->orWhere('name', $identifier)
                ->first();

        return $program?->code ?? '101';
    }

    public function resolveUnitCode(int|string|null $identifier): string
    {
        if (empty($identifier)) {
            return '501';
        }

        $unit = is_numeric($identifier)
            ? WorkUnit::find($identifier)
            : WorkUnit::where('code', $identifier)
                ->orWhere('name', $identifier)
                ->first();

        return $unit?->code ?? '501';
    }

    /**
     * Generate the next available sequential number for a given prefix.
     */
    private function generateSequentialNumber(string $prefix, int $sequenceLength = 4): string
    {
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
