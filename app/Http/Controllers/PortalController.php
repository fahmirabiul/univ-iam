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
        $user = $request->user()->loadMissing(['profile', 'roles']);
        $primaryRole = $user->roles->first()?->name ?? 'user';

        $apps = $this->getAvailableApplications($primaryRole);

        return view('portal.index', [
            'user' => $user,
            'profile' => $user->profile,
            'primaryRole' => $primaryRole,
            'apps' => $apps,
        ]);
    }

    /**
     * Get the directory of applications filtered by user role.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getAvailableApplications(string $role): array
    {
        $allApps = [
            [
                'id' => 'knowledge-hub',
                'name' => 'Knowledge Hub',
                'description' => 'Pusat repositori materi ajar, modul digital, dan kolaborasi riset sivitas.',
                'icon' => 'tabler-book-2',
                'color' => 'primary',
                'category' => 'Akademik & Riset',
                'roles' => ['super_admin', 'admin_sdm', 'dosen', 'mahasiswa', 'karyawan'],
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
                'roles' => ['super_admin', 'admin_sdm'],
                'url' => '#',
                'is_sso' => false,
            ],
        ];

        return array_values(array_filter($allApps, fn (array $app) => in_array($role, $app['roles'], true)));
    }
}
