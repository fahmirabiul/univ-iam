<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    /**
     * Display the SSO Application Portal Dashboard.
     */
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user()->loadMissing(['profile.workUnit', 'profile.studyProgram.faculty', 'roles']);
        $primaryRole = $user->getCivitasRole()?->name ?? ($user->roles->first()?->name ?? 'user');

        $apps = $this->getAvailableApplications($user);

        return view('portal.index', [
            'user' => $user,
            'profile' => $user->profile,
            'primaryRole' => $primaryRole,
            'apps' => $apps,
        ]);
    }

    /**
     * Get the directory of applications filtered by user roles and unit admin rights.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAvailableApplications(User $user): array
    {
        $userRoles = $user->roles->pluck('name')->all();

        $allApps = [
            [
                'id' => 'knowledge-hub',
                'name' => 'Knowledge Hub',
                'description' => 'Pusat repositori materi ajar, modul digital, dan kolaborasi riset sivitas.',
                'icon' => 'tabler-book-2',
                'color' => 'primary',
                'category' => 'Akademik & Riset',
                'roles' => ['super_admin', 'dosen', 'mahasiswa', 'karyawan'],
                'url' => 'http://localhost:8001',
                'is_sso' => true,
            ],
            [
                'id' => 'siakad',
                'name' => 'Sistem Informasi Akademik (SIAKAD)',
                'description' => 'Pengisian KRS, penilaian perkuliahan, dan pencatatan presensi mahasiswa.',
                'icon' => 'tabler-calendar-stats',
                'color' => 'info',
                'category' => 'Layanan Perkuliahan',
                'roles' => ['super_admin', 'dosen', 'mahasiswa'],
                'url' => '#',
                'is_sso' => true,
            ],
            [
                'id' => 'e-library',
                'name' => 'Perpustakaan Digital',
                'description' => 'Akses koleksi e-book, jurnal internasional, dan peminjaman pustaka online.',
                'icon' => 'tabler-building-bank',
                'color' => 'success',
                'category' => 'Pustaka & Referensi',
                'roles' => ['super_admin', 'dosen', 'mahasiswa', 'karyawan'],
                'url' => '#',
                'is_sso' => true,
            ],
            [
                'id' => 'sdm-portal',
                'name' => 'Manajemen Kepegawaian & SDM',
                'description' => 'Pengelolaan master data dosen, kenaikan jabatan, dan administrasi staf.',
                'icon' => 'tabler-users-group',
                'color' => 'warning',
                'category' => 'Administrasi SDM',
                'roles' => ['super_admin'],
                'units' => ['502'],
                'url' => '#',
                'is_sso' => false,
            ],
            [
                'id' => 'lppm-portal',
                'name' => 'Sistem Informasi Riset & LPPM',
                'description' => 'Pengajuan proposal hibah penelitian, pengabdian masyarakat, dan publikasi ilmiah.',
                'icon' => 'tabler-microscope',
                'color' => 'info',
                'category' => 'Riset & Pengabdian',
                'roles' => ['super_admin', 'dosen'],
                'units' => ['503'],
                'url' => '#',
                'is_sso' => false,
            ],
        ];

        return array_values(array_filter(
            $allApps,
            function (array $app) use ($user, $userRoles): bool {
                if ($user->isSuperAdmin()) {
                    return true;
                }

                if (! empty(array_intersect($userRoles, $app['roles'] ?? []))) {
                    return true;
                }

                if ((bool) $user->is_admin && ! empty($app['units'])) {
                    $unitCode = $user->profile?->workUnit?->code;
                    return in_array($unitCode, $app['units'], true);
                }

                return false;
            }
        ));
    }
}
