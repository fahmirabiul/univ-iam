@extends('layouts.auth')

@section('title', 'Masuk ke Portal Sivitas')

@section('content')
<div class="card">
    <div class="card-body p-6 p-md-8">
        <!-- Logo Brand -->
        <div class="app-brand justify-content-center mb-6">
            <a href="{{ url('/') }}" class="app-brand-link gap-2 text-decoration-none">
                <span class="app-brand-logo demo">
                    <span class="text-primary">
                        <svg width="34" height="26" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor" />
                        </svg>
                    </span>
                </span>
                <span class="app-brand-text demo text-heading fw-bold fs-4">Univ IAM</span>
            </a>
        </div>

        <h4 class="mb-1 text-heading fw-bold">Portal Sivitas Akademika</h4>
        <p class="mb-6 text-body">Gunakan akun kampus resmi Anda untuk mengakses seluruh ekosistem layanan universitas.</p>

        @if(session('status'))
            <div class="alert alert-success alert-dismissible mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="icon-base ti tabler-circle-check me-2"></i>
                    <span>{{ session('status') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="icon-base ti tabler-alert-circle me-2"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form id="formAuthentication" class="mb-4" action="{{ route('login.attempt') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label text-heading fw-medium">Alamat Email Kampus</label>
                <input
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="nama@univ.ac.id"
                    required
                    autofocus />
            </div>

            <div class="mb-4 form-password-toggle">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label text-heading fw-medium mb-0" for="password">Kata Sandi</label>
                </div>
                <div class="input-group input-group-merge">
                    <input
                        type="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password"
                        placeholder="••••••••••••"
                        required
                        aria-describedby="togglePassword" />
                    <span class="input-group-text cursor-pointer" id="togglePassword">
                        <i class="icon-base ti tabler-eye-off" id="togglePasswordIcon"></i>
                    </span>
                </div>
            </div>

            <div class="my-4 d-flex justify-content-between align-items-center">
                <div class="form-check mb-0">
                    <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} />
                    <label class="form-check-label text-body" for="remember-me">Ingat saya di perangkat ini</label>
                </div>
            </div>

            <div class="mb-4">
                <button class="btn btn-primary d-grid w-100 py-2 fw-semibold" type="submit">
                    Masuk ke Portal
                </button>
            </div>
        </form>

        <!-- Quick Demo Credentials Box -->
        <div class="card bg-light border-0 mt-4">
            <div class="card-body p-3">
                <p class="text-xs text-uppercase text-muted fw-semibold mb-2">Akun Demo Pengujian:</p>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-xs btn-outline-primary demo-pill" onclick="fillCredential('fahmi.dosen@univ.ac.id', 'password')">
                        <i class="icon-base ti tabler-school me-1"></i> Dosen
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-success demo-pill" onclick="fillCredential('ahmad.mhs@univ.ac.id', 'password')">
                        <i class="icon-base ti tabler-user me-1"></i> Mahasiswa
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning demo-pill" onclick="fillCredential('sdm@univ.ac.id', 'password')">
                        <i class="icon-base ti tabler-shield me-1"></i> Admin SDM
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (togglePassword && passwordInput && toggleIcon) {
            togglePassword.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleIcon.classList.toggle('tabler-eye-off', !isPassword);
                toggleIcon.classList.toggle('tabler-eye', isPassword);
            });
        }
    });

    function fillCredential(email, password) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        if (emailInput && passwordInput) {
            emailInput.value = email;
            passwordInput.value = password;
            emailInput.focus();
        }
    }
</script>
@endpush
