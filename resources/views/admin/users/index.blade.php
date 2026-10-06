@extends('layouts.portal')

@section('title', 'Manajemen Data Master Sivitas')

@section('content')
<!-- Page Header & Action Bar -->
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-heading mb-1">Manajemen Master Data Sivitas Akademika</h4>
            <p class="text-body mb-0">Kelola akun pengguna, penetapan peran global, dan status kepegawaian/akademik terpusat.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
                <i class="icon-base ti tabler-user-plus fs-5"></i>
                <span>Tambah Sivitas</span>
            </button>
        </div>
    </div>
</div>

<!-- Flash Alerts -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="icon-base ti tabler-circle-check fs-5 text-success"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="icon-base ti tabler-alert-circle fs-5 text-danger"></i>
            <strong>Terjadi kesalahan saat memproses data:</strong>
        </div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

<!-- Filter & Search Toolbar Partial -->
@include('admin.users.partials.filter-toolbar')

<!-- Users Data Table Card -->
<div class="card bg-white border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-heading mb-0">Daftar Akun Terdaftar ({{ $users->total() }} Total)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="py-3 px-4 text-heading fw-semibold small text-uppercase">Pengguna</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Unit / Program Studi</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Peran & Hak Akses</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Status Akademik</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Status Akun</th>
                    <th scope="col" class="py-3 px-4 text-end text-heading fw-semibold small text-uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <!-- User Identity & Email -->
                        <td class="py-3 px-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-initial flex-shrink-0" style="width: 36px; height: 36px; font-size: 0.875rem;">
                                    {{ strtoupper(substr($user->profile?->nama_lengkap ?? $user->email, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-heading">{{ $user->profile?->nama_lengkap ?? 'Tanpa Nama' }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Identifier & Unit / Study Program -->
                        <td class="py-3 px-3">
                            <div class="fw-medium text-heading small">{{ $user->profile?->nomor_induk ?? '-' }}</div>
                            <div class="text-muted small">
                                {{ $user->profile?->fakultas ? $user->profile->fakultas . ($user->profile->program_studi ? ' • ' . $user->profile->program_studi : '') : ($user->profile?->unit_kerja ?? 'Pusat') }}
                            </div>
                        </td>

                        <!-- Roles (Civitas Identity + Admin Roles) -->
                        <td class="py-3 px-3">
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                <x-role-badge :role="$user->getCivitasRole()?->name ?? 'user'" />
                                @foreach($user->getAdminRoles() as $ar)
                                    <x-role-badge :role="$ar->name" />
                                @endforeach
                            </div>
                        </td>

                        <!-- Academic Status -->
                        <td class="py-3 px-3">
                            <x-status-badge :status="$user->profile?->status_akademik ?? 'aktif'" />
                        </td>

                        <!-- Account Active State -->
                        <td class="py-3 px-3">
                            @if($user->is_active)
                                <span class="badge bg-label-success">Aktif</span>
                            @else
                                <span class="badge bg-label-danger">Dinonaktifkan</span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <!-- Trigger Manage Admin Roles Modal (Khusus Karyawan / Dosen) -->
                                @if(in_array($user->getCivitasRole()?->name, ['karyawan', 'dosen'], true))
                                    <button type="button"
                                            class="btn btn-sm btn-outline-info fw-semibold px-2 btn-manage-roles"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalManageRoles"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->profile?->nama_lengkap ?? $user->email }}"
                                            data-civitas="{{ $user->getCivitasRole()?->name ?? 'karyawan' }}"
                                            data-admin-roles="{{ json_encode($user->getAdminRoles()->pluck('name')->all()) }}"
                                            data-action-url="{{ route('admin.users.update_roles', $user) }}"
                                            title="Kelola Peran Admin">
                                        <i class="icon-base ti tabler-user-shield me-1"></i>Role Admin
                                    </button>
                                @endif

                                <!-- Trigger Dynamic Status Modal -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary fw-semibold px-3 btn-edit-status"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalUpdateStatus"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->profile?->nama_lengkap ?? $user->email }}"
                                        data-role="{{ $user->getCivitasRole()?->name ?? 'dosen' }}"
                                        data-status="{{ $user->profile?->status_akademik ?? 'aktif' }}"
                                        data-active="{{ $user->is_active ? '1' : '0' }}"
                                        data-action-url="{{ route('admin.users.update_status', $user) }}">
                                    <i class="icon-base ti tabler-edit me-1"></i>Status
                                </button>

                                <!-- Trigger Delete Form -->
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun ini?');" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pengguna">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="mb-3">
                                <i class="icon-base ti tabler-search-off fs-1 text-muted"></i>
                            </div>
                            <h6 class="fw-bold text-heading">Tidak Ada Data Sivitas yang Cocok</h6>
                            <p class="text-muted small mb-0">Coba ubah kata kunci pencarian atau sesuaikan opsi filter Anda.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted">
                Menampilkan {{ $users->firstItem() ?? 0 }} sampai {{ $users->lastItem() ?? 0 }} dari {{ $users->total() }} data
            </span>
            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Partials -->
@include('admin.users.partials.create-user-modal')
@include('admin.users.partials.update-status-modal')
@include('admin.users.partials.manage-roles-modal')
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalUpdateStatus = document.getElementById('modalUpdateStatus');
        const modalManageRoles = document.getElementById('modalManageRoles');
        const statusOptions = @json($statusOptions ?? []);

        if (modalManageRoles) {
            modalManageRoles.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const userName = button.getAttribute('data-user-name') || '';
                const civitasRole = button.getAttribute('data-civitas') || 'karyawan';
                const actionUrl = button.getAttribute('data-action-url') || '';
                const activeAdminRoles = JSON.parse(button.getAttribute('data-admin-roles') || '[]');

                const form = modalManageRoles.querySelector('#formManageRoles');
                const nameEl = modalManageRoles.querySelector('#modalRolesUserName');
                const civitasBadge = modalManageRoles.querySelector('#modalRolesCivitasBadge');

                if (form) form.action = actionUrl;
                if (nameEl) nameEl.textContent = userName;
                if (civitasBadge) {
                    civitasBadge.innerHTML = `<span class="badge bg-label-warning text-uppercase fw-semibold px-2 py-1">${civitasRole.toUpperCase()}</span>`;
                }

                // Check or uncheck admin roles checkboxes
                const checkboxes = modalManageRoles.querySelectorAll('.admin-role-checkbox');
                checkboxes.forEach(function (cb) {
                    cb.checked = activeAdminRoles.includes(cb.value);
                });
            });
        }

        if (modalUpdateStatus) {
            modalUpdateStatus.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const userName = button.getAttribute('data-user-name') || '';
                const userRole = button.getAttribute('data-role') || 'dosen';
                const currentStatus = button.getAttribute('data-status') || 'aktif';
                const isActive = button.getAttribute('data-active') === '1';
                const actionUrl = button.getAttribute('data-action-url') || '';

                const form = modalUpdateStatus.querySelector('#formUpdateStatus');
                const nameEl = modalUpdateStatus.querySelector('#modalStatusUserName');
                const statusSelect = modalUpdateStatus.querySelector('#modalStatusAkademik');
                const activeCheckbox = modalUpdateStatus.querySelector('#modalStatusIsActive');

                if (form) form.action = actionUrl;
                if (nameEl) nameEl.textContent = userName + ' (' + userRole.toUpperCase() + ')';
                if (activeCheckbox) activeCheckbox.checked = isActive;

                if (statusSelect) {
                    statusSelect.innerHTML = '';
                    const roleOpts = statusOptions[userRole] || statusOptions['dosen'] || { aktif: 'Aktif' };
                    for (const [val, label] of Object.entries(roleOpts)) {
                        const optEl = document.createElement('option');
                        optEl.value = val;
                        optEl.textContent = label;
                        if (val === currentStatus) optEl.selected = true;
                        statusSelect.appendChild(optEl);
                    }
                }
            });
        }
    });
</script>
@endpush
