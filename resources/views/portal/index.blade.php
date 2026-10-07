@extends('layouts.portal')

@section('title', 'Portal Layanan Sivitas')

@section('content')
<!-- Welcome Banner -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card bg-white border-0 shadow-sm p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold text-heading mb-0">Selamat Datang, {{ $profile->nama_lengkap ?? $user->email }}!</h4>
                        @if($user->isSuperAdmin())
                            <span class="badge bg-label-danger text-uppercase" style="font-size: 0.75rem;">SUPER ADMIN</span>
                        @elseif($user->is_admin)
                            <span class="badge bg-label-warning text-uppercase" style="font-size: 0.75rem;">ADMIN UNIT</span>
                        @endif
                    </div>
                    <p class="text-body mb-0">
                        {{ $profile?->studyProgram ? ($profile->studyProgram->faculty?->name ? $profile->studyProgram->faculty->name . ' • ' : '') . $profile->studyProgram->name : ($profile?->workUnit?->name ?? 'Sivitas Akademika') }}
                    </p>
                </div>
                <div>
                    <span class="badge bg-label-primary px-3 py-2 fs-6">
                        <i class="icon-base ti tabler-shield-check me-1"></i>
                        Peran: <span class="text-uppercase fw-bold">{{ str_replace('_', ' ', $primaryRole) }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Title -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold text-heading mb-1">Direktori Aplikasi & Layanan Terpadu</h5>
        <p class="text-muted small mb-0">Klik aplikasi untuk langsung mengakses sistem dengan otentikasi terpusat (Single Sign-On).</p>
    </div>
    <span class="badge bg-label-secondary text-heading border px-3 py-2 fw-semibold">
        <i class="icon-base ti tabler-apps me-1 text-primary"></i>
        {{ count($apps) }} Aplikasi Tersedia
    </span>
</div>

<!-- App Launcher Grid -->
<div class="row g-4">
    @forelse($apps as $app)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-app h-100 p-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="app-icon-container bg-label-{{ $app['color'] }}">
                            <i class="icon-base ti {{ $app['icon'] }} fs-3"></i>
                        </div>
                        <span class="badge bg-label-secondary" style="font-size: 0.75rem;">
                            {{ $app['category'] }}
                        </span>
                    </div>
                    <h5 class="fw-bold text-heading mb-2">{{ $app['name'] }}</h5>
                    <p class="text-body small mb-4">{{ $app['description'] }}</p>
                </div>

                <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                    <span class="small text-muted">
                        @if($app['is_sso'])
                            <i class="icon-base ti tabler-key text-primary me-1"></i>SSO Ready
                        @else
                            <i class="icon-base ti tabler-lock me-1"></i>Internal
                        @endif
                    </span>
                    <a href="{{ $app['url'] }}" class="btn btn-sm btn-outline-primary fw-semibold px-3" target="{{ $app['url'] !== '#' ? '_blank' : '_self' }}">
                        Buka Aplikasi
                        <i class="icon-base ti tabler-arrow-up-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card text-center p-5 border-0">
                <div class="mb-3">
                    <i class="icon-base ti tabler-apps-off fs-1 text-muted"></i>
                </div>
                <h5 class="fw-bold text-heading">Belum Ada Aplikasi yang Terhubung</h5>
                <p class="text-muted mb-0">Saat ini belum ada aplikasi operasional yang dikonfigurasikan untuk peran Anda.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection
