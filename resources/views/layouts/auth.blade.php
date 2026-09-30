<!doctype html>
<html lang="id" class="layout-wide customizer-hide" dir="ltr" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'Login') | Univ IAM</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icon Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/iconify-icons.css') }}" />

    <!-- Core Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/core.css') }}" />

    <!-- Page Specific CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/pages/page-auth.css') }}" />

    <style>
        body {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f7fa;
            color: #6f6b7d;
        }
        .text-heading {
            color: #4b465c !important;
        }
        .btn-primary {
            background-color: #7367f0;
            border-color: #7367f0;
            box-shadow: 0 0.125rem 0.25rem rgba(115, 103, 240, 0.4);
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #5e50ee !important;
            border-color: #5e50ee !important;
        }
        .card {
            border: 1px solid #dbdade;
            box-shadow: 0 0.25rem 1.125rem rgba(75, 70, 92, 0.1);
            border-radius: 0.75rem;
        }
        .form-control:focus {
            border-color: #7367f0;
            box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.18);
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .demo-pill {
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .demo-pill:hover {
            transform: translateY(-1px);
        }
    </style>

    @stack('page-css')
</head>
<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Core JavaScript -->
    <script src="{{ asset('assets/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/js/helpers.js') }}"></script>

    @stack('page-js')
</body>
</html>
