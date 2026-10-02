@extends('layouts.portal')

@section('title', 'Manajemen OAuth2 Client')

@section('content')
<!-- Page Header & Action Bar -->
<div class="row mb-4">
    <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h4 class="fw-bold text-heading mb-1">Manajemen Aplikasi Klien OAuth2 (SSO Clients)</h4>
            <p class="text-body mb-0">Kelola aplikasi ekosistem kampus (Knowledge Hub, SIAKAD, dsb.) yang diizinkan menggunakan otentikasi terpusat.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCreateClient">
                <i class="icon-base ti tabler-plus fs-5"></i>
                <span>Daftarkan Klien Baru</span>
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
            <strong>Terjadi kesalahan saat memproses pendaftaran klien:</strong>
        </div>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

<!-- Clients Table Card -->
<div class="card bg-white border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-heading mb-0">Daftar Aplikasi Klien Terdaftar ({{ $clients->total() }} Total)</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col" class="py-3 px-4 text-heading fw-semibold small text-uppercase">Nama Aplikasi</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Client ID (UUID)</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">URL Callback (Redirect URI)</th>
                    <th scope="col" class="py-3 px-3 text-heading fw-semibold small text-uppercase">Tipe Klien</th>
                    <th scope="col" class="py-3 px-4 text-end text-heading fw-semibold small text-uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <!-- App Name & Icon -->
                        <td class="py-3 px-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="app-icon-container bg-label-primary flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="icon-base ti tabler-device-desktop fs-4 text-primary"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-heading">{{ $client->name }}</div>
                                    <small class="text-muted">Didaftarkan: {{ $client->created_at?->format('d M Y') ?? '-' }}</small>
                                </div>
                            </div>
                        </td>

                        <!-- Client ID (UUID) -->
                        <td class="py-3 px-3">
                            <div class="d-flex align-items-center gap-2">
                                <code class="text-dark small px-2 py-1 bg-light rounded border">{{ $client->id }}</code>
                                <button type="button" class="btn btn-sm btn-link text-muted p-0" onclick="navigator.clipboard.writeText('{{ $client->id }}'); this.title='Tersalin!';" title="Salin Client ID">
                                    <i class="icon-base ti tabler-copy"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Redirect URIs -->
                        <td class="py-3 px-3">
                            <div class="small text-body" style="max-width: 320px; word-break: break-all;">
                                @php
                                    $uris = is_array($client->redirect_uris) ? $client->redirect_uris : explode(',', (string) ($client->redirect ?? ''));
                                @endphp
                                @foreach(array_filter($uris) as $uri)
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <i class="icon-base ti tabler-link text-muted" style="font-size: 0.85rem;"></i>
                                        <span>{{ trim($uri) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </td>

                        <!-- Client Type -->
                        <td class="py-3 px-3">
                            @if(!empty($client->secret))
                                <span class="badge bg-label-success d-inline-flex align-items-center gap-1">
                                    <i class="icon-base ti tabler-lock"></i>
                                    <span>Confidential</span>
                                </span>
                            @else
                                <span class="badge bg-label-info d-inline-flex align-items-center gap-1">
                                    <i class="icon-base ti tabler-lock-open"></i>
                                    <span>Public</span>
                                </span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3 px-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <!-- Regenerate Secret Form -->
                                @if(!empty($client->secret))
                                    <form action="{{ route('admin.clients.regenerate_secret', $client) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memperbarui Client Secret? Klien dengan secret lama tidak akan bisa login sampai konfigurasi diperbarui.');" class="d-inline m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning fw-semibold px-2 py-1" title="Perbarui Secret">
                                            <i class="icon-base ti tabler-refresh me-1"></i>Reset Secret
                                        </button>
                                    </form>
                                @endif

                                <!-- Revoke / Delete Form -->
                                <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut (revoke) aplikasi klien ini?');" class="d-inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Cabut Izin Klien">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="mb-3">
                                <i class="icon-base ti tabler-apps-off fs-1 text-muted"></i>
                            </div>
                            <h6 class="fw-bold text-heading">Belum Ada Aplikasi Klien Terdaftar</h6>
                            <p class="text-muted small mb-0">Klik tombol "Daftarkan Klien Baru" di atas untuk mendaftarkan aplikasi seperti Knowledge Hub atau SIAKAD.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    @if($clients->hasPages())
        <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
            <span class="small text-muted">
                Menampilkan {{ $clients->firstItem() ?? 0 }} sampai {{ $clients->lastItem() ?? 0 }} dari {{ $clients->total() }} klien
            </span>
            <div>
                {{ $clients->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

<!-- Modal Partials -->
@include('admin.clients.partials.create-client-modal')
@include('admin.clients.partials.secret-revealer-modal')
@endsection
