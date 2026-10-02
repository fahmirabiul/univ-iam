@extends('layouts.auth')

@section('title', 'Otorisasi Akses Single Sign-On')

@section('content')
<div class="card p-md-5 p-4">
    <div class="card-body">
        <!-- Logo Univ IAM -->
        <div class="app-brand justify-content-center mb-4">
            <a href="{{ route('portal') }}" class="app-brand-link gap-2 text-decoration-none">
                <span class="app-brand-logo text-primary">
                    <svg width="36" height="26" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor" />
                    </svg>
                </span>
                <span class="app-brand-text fw-bold fs-4 text-heading">Univ IAM</span>
            </a>
        </div>

        <div class="text-center mb-4">
            <h4 class="mb-1 fw-bold text-heading">Permintaan Otorisasi Akses</h4>
            <p class="text-muted small mb-0">Aplikasi eksternal meminta izin untuk menghubungkan akun sivitas Anda.</p>
        </div>

        <!-- Target Client Card -->
        <div class="p-3 bg-light rounded border mb-4 text-center">
            <div class="d-inline-flex align-items-center justify-content-center bg-label-primary rounded-circle mb-2" style="width: 48px; height: 48px;">
                <i class="icon-base ti tabler-apps fs-3 text-primary"></i>
            </div>
            <h5 class="fw-bold text-heading mb-1">{{ $client->name }}</h5>
            <small class="text-muted d-block font-monospace" style="font-size: 0.75rem;">
                {{ parse_url($client->redirect, PHP_URL_HOST) ?? 'Aplikasi Terverifikasi Ekosistem Kampus' }}
            </small>
        </div>

        <!-- Current User Info -->
        <div class="d-flex align-items-center gap-3 p-3 border rounded mb-4 bg-white">
            <div class="avatar-initial flex-shrink-0" style="width: 40px; height: 40px; font-size: 0.95rem;">
                {{ strtoupper(substr($user->profile?->nama_lengkap ?? $user->email, 0, 2)) }}
            </div>
            <div class="overflow-hidden">
                <div class="fw-semibold text-heading text-truncate">{{ $user->profile?->nama_lengkap ?? $user->email }}</div>
                <div class="text-muted small text-truncate">
                    {{ $user->email }}
                    @if($user->profile?->nomor_induk)
                        • {{ $user->profile->nomor_induk }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Permission Scopes List -->
        <div class="mb-4">
            <div class="fw-semibold text-heading small mb-2">Aplikasi ini akan mendapatkan izin untuk:</div>
            <ul class="list-group list-group-flush border rounded">
                <li class="list-group-item d-flex align-items-start gap-2 py-3">
                    <i class="icon-base ti tabler-id-badge fs-5 text-primary flex-shrink-0 mt-1"></i>
                    <div class="small">
                        <strong class="d-block text-heading">Identitas Akun Dasar</strong>
                        <span>Membaca UUID SSO, nama lengkap, dan alamat email kampus Anda.</span>
                    </div>
                </li>
                <li class="list-group-item d-flex align-items-start gap-2 py-3">
                    <i class="icon-base ti tabler-school fs-5 text-primary flex-shrink-0 mt-1"></i>
                    <div class="small">
                        <strong class="d-block text-heading">Data Demografi & Status Akademik</strong>
                        <span>Melihat data fakultas, program studi, unit kerja, dan status keaktifan sivitas.</span>
                    </div>
                </li>
            </ul>
        </div>

        <div class="alert alert-secondary border-0 p-2 mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="icon-base ti tabler-shield-check text-success fs-5 flex-shrink-0"></i>
            <small class="text-muted" style="font-size: 0.75rem;">
                Univ IAM menjamin kata sandi Anda tidak akan dibagikan ke aplikasi di atas.
            </small>
        </div>

        <!-- Action Form Buttons (Approve / Deny) -->
        <div class="row g-2">
            <!-- Approve Form -->
            <div class="col-6">
                <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="state" value="{{ $request->state }}" />
                    <input type="hidden" name="client_id" value="{{ $client->id }}" />
                    <input type="hidden" name="auth_token" value="{{ $authToken }}" />
                    <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                        <i class="icon-base ti tabler-check me-1"></i>Izinkan
                    </button>
                </form>
            </div>

            <!-- Deny Form -->
            <div class="col-6">
                <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="m-0">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="{{ $request->state }}" />
                    <input type="hidden" name="client_id" value="{{ $client->id }}" />
                    <input type="hidden" name="auth_token" value="{{ $authToken }}" />
                    <button type="submit" class="btn btn-outline-secondary w-100 fw-semibold py-2">
                        <i class="icon-base ti tabler-x me-1"></i>Tolak
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
