@extends('layouts.portal')

@section('title', 'Manajemen Data Master Sivitas')

@section('content')
<!-- Page Header & Action Bar -->
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-heading mb-1">Manajemen Master Data Sivitas Akademika</h4>
            <p class="text-body mb-0">Kelola akun pengguna, penetapan peran sivitas, dan hak admin unit kerja terpusat.</p>
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
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Nomor Induk / Penempatan</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Peran Sivitas</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Wewenang Admin</th>
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
                                @if($user->profile?->studyProgram)
                                    {{ $user->profile->studyProgram->faculty?->name ? $user->profile->studyProgram->faculty->name . ' • ' : '' }}{{ $user->profile->studyProgram->name }}
                                @else
                                    {{ $user->profile?->workUnit?->name ?? 'Pusat' }}
                                @endif
                            </div>
                        </td>

                        <!-- Primary Civitas Role -->
                        <td class="py-3 px-3">
                            <x-role-badge :role="$user->getCivitasRole()?->name ?? 'user'" />
                        </td>

                        <!-- Admin Authority -->
                        <td class="py-3 px-3">
                            @if($user->isSuperAdmin())
                                <span class="badge bg-label-danger fw-semibold">
                                    <i class="icon-base ti tabler-shield-lock me-1"></i>Super Admin
                                </span>
                            @elseif($user->is_admin)
                                <span class="badge bg-label-warning fw-semibold">
                                    <i class="icon-base ti tabler-shield-check me-1"></i>Admin Unit
                                </span>
                            @else
                                <span class="badge bg-label-secondary">Staf / Anggota</span>
                            @endif
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
                                <!-- Trigger Update Status & Admin Modal -->
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary fw-semibold px-3 btn-edit-status"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalUpdateStatus"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->profile?->nama_lengkap ?? $user->email }}"
                                        data-role="{{ $user->getCivitasRole()?->name ?? 'karyawan' }}"
                                        data-is-admin="{{ $user->is_admin ? '1' : '0' }}"
                                        data-active="{{ $user->is_active ? '1' : '0' }}"
                                        data-is-superadmin="{{ $user->isSuperAdmin() ? '1' : '0' }}"
                                        data-action-url="{{ route('admin.users.update_status', $user) }}">
                                    <i class="icon-base ti tabler-edit me-1"></i>Ubah
                                </button>

                                <!-- Trigger Delete Form -->
                                @if(! $user->isSuperAdmin())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan akun ini?');" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pengguna">
                                            <i class="icon-base ti tabler-trash"></i>
                                        </button>
                                    </form>
                                @endif
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
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalUpdateStatus = document.getElementById('modalUpdateStatus');

        if (modalUpdateStatus) {
            modalUpdateStatus.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const userName = button.getAttribute('data-user-name') || '';
                const userRole = button.getAttribute('data-role') || 'karyawan';
                const isActive = button.getAttribute('data-active') === '1';
                const isAdmin = button.getAttribute('data-is-admin') === '1';
                const isSuperAdmin = button.getAttribute('data-is-superadmin') === '1';
                const actionUrl = button.getAttribute('data-action-url') || '';

                const form = modalUpdateStatus.querySelector('#formUpdateStatus');
                const nameEl = modalUpdateStatus.querySelector('#modalStatusUserName');
                const activeCheckbox = modalUpdateStatus.querySelector('#modalStatusIsActive');
                const adminCheckbox = modalUpdateStatus.querySelector('#modalStatusIsAdmin');
                const adminContainer = modalUpdateStatus.querySelector('#modalStatusIsAdminContainer');

                if (form) form.action = actionUrl;
                if (nameEl) nameEl.textContent = userName + ' (' + userRole.toUpperCase() + ')';
                if (activeCheckbox) activeCheckbox.checked = isActive;
                if (adminCheckbox) adminCheckbox.checked = isAdmin;

                if (adminContainer) {
                    // Hide admin checkbox for super_admin since they already have global authority
                    adminContainer.style.display = isSuperAdmin ? 'none' : 'block';
                }
            });
        }
    });
</script>
@endpush
