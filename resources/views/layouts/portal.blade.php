<!doctype html>
<html lang="id" class="layout-navbar-fixed layout-wide" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'Portal Layanan') | Univ IAM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icon Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/iconify-icons.css') }}" />

    <!-- Core Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/core.css') }}" />

    <style>
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f7fa;
            color: #6f6b7d;
        }
        .navbar-portal {
            background-color: #ffffff;
            border-bottom: 1px solid #dbdade;
            box-shadow: 0 0.125rem 0.25rem rgba(165, 163, 174, 0.12);
        }
        .text-heading {
            color: #4b465c !important;
        }
        .card {
            border: 1px solid #dbdade;
            box-shadow: 0 0.25rem 1rem rgba(75, 70, 92, 0.08);
            border-radius: 0.75rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-app:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.5rem 1.5rem rgba(115, 103, 240, 0.15);
            border-color: #7367f0;
        }
        .app-icon-container {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }
        .bg-label-primary {
            background-color: rgba(115, 103, 240, 0.12) !important;
            color: #7367f0 !important;
        }
        .bg-label-info {
            background-color: rgba(0, 207, 232, 0.12) !important;
            color: #00cfe8 !important;
        }
        .bg-label-success {
            background-color: rgba(40, 199, 111, 0.12) !important;
            color: #28c76f !important;
        }
        .bg-label-warning {
            background-color: rgba(255, 159, 67, 0.12) !important;
            color: #ff9f43 !important;
        }
        .avatar-initial {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border-radius: 50%;
            background-color: #7367f0;
            color: #ffffff;
        }
    </style>

    @stack('page-css')
</head>
<body>
    <!-- Topbar Navigation -->
    <nav class="navbar navbar-expand-lg navbar-portal sticky-top py-3">
        <div class="container-xxl">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('portal') }}">
                <span class="text-primary">
                    <svg width="32" height="24" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z" fill="currentColor" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z" fill="currentColor" />
                    </svg>
                </span>
                <span class="fw-bold fs-5 text-heading">Univ IAM</span>
                <span class="badge bg-label-primary ms-1 d-none d-sm-inline-block">Portal SSO</span>
            </a>

            <!-- Right Profile Dropdown -->
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-semibold text-heading small">{{ auth()->user()->profile->nama_lengkap ?? auth()->user()->email }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">
                        {{ auth()->user()->profile->nomor_induk ? auth()->user()->profile->nomor_induk . ' • ' : '' }}
                        <span class="text-uppercase fw-semibold text-primary">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
                    </div>
                </div>

                <div class="dropdown">
                    <button class="btn btn-link p-0 border-0 text-decoration-none dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-initial">
                            {{ strtoupper(substr(auth()->user()->profile->nama_lengkap ?? auth()->user()->email, 0, 2)) }}
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold text-heading">{{ auth()->user()->profile->nama_lengkap ?? auth()->user()->email }}</div>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                    <i class="icon-base ti tabler-logout"></i>
                                    <span>Keluar dari Sesi</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container-xxl">
            @yield('content')
        </div>
    </main>

    <!-- Core JavaScript -->
    <script src="{{ asset('assets/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/js/helpers.js') }}"></script>

    @stack('page-js')
</body>
</html>
