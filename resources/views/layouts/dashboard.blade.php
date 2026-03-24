<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', $settings->site_name ?? config('app.name'))</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/vendor.bundle.base.css') }}">
    <!-- endinject -->

    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datepicker.min.css') }}">
    <!-- End plugin css for this page -->
    <link rel="shortcut icon"
        href="{{ asset('assets/images/' . $settings->favicon ?? 'assets/images/default_favicon.png') }}">
    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- endinject -->
    <link rel="stylesheet" href="{{ asset('assets/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @stack('styles')
    <style>
        /* Sidebar base styling */
        .sidebar {
            background-color: #1e3a8a;
            color: #ecf0f1;
            font-family: Arial, sans-serif;
            /* height: 100vh; */
            padding-top: 7px;
            padding-bottom: 10px;
        }

        .sidebar .nav-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #cbd5e1; /* Slate-300 for neutral text */
            text-transform: none;
            font-weight: 500;
            margin: 4px 12px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            position: relative;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(37, 99, 235, 0.2); /* Soft primary blue tint */
            color: #ffffff;
        }

        .sidebar .nav-link::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            height: 0;
            width: 4px;
            background-color: #3b82f6;
            border-radius: 0 4px 4px 0;
            transition: all 0.2s ease;
            transform: translateY(-50%);
            opacity: 0;
        }

        .sidebar .nav-link:hover::before {
            height: 20px;
            opacity: 1;
        }

        .sidebar .nav-link.active {
            background-color: #2563eb;
            color: #fff;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .sidebar .nav-link.active::before {
            height: 24px;
            background-color: #fff;
            opacity: 1;
        }

        .sidebar .nav .nav-item .nav-link .menu-title {
            color: #fff;
        }

        .sidebar .menu-icon {
            margin-right: 15px;
            font-size: 20px;
        }

        .sidebar .menu-title {
            font-weight: 500;
        }

        /* Remove sub-menu and nested styling
        .sidebar .sub-menu {
            display: none;
        } */
        .sidebar .sub-menu {
            display: none;
            padding-left: 2rem;
            /* background-color: #111; */
        }

        .sidebar .sub-menu.show {
            display: block;
        }

        /* For collapsible section, remove icon rotation */
        .menu-arrow {
            display: none;
        }

        /* Hide sidebar-profile-info when sidebar is collapsed */
        .sidebar-collapse-hide {
            display: block;
            /* Show by default */
        }

        /* Hide sidebar-profile-info when sidebar is collapsed */
        .sidebar.collapsed .sidebar-profile-info {
            display: none !important;
        }

        .sidebar.collapsed .sidebar-profile {
            display: none !important;
        }

        .truncate-text {
            display: block;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-profile-name,
        .sidebar-profile-email small {
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {

            .sidebar-profile-name,
            .sidebar-profile-email small {
                font-size: 0.8rem;
            }
        }


        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(37, 211, 102, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(37, 211, 102, 0);
            }
        }

        .whatsapp-float.pulse {
            animation: pulse 2s infinite;
        }

        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
            background-color: #25D366;
            color: white;
            border-radius: 50%;
            text-align: center;
            font-size: 28px;
            z-index: 9999;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease-in-out;
        }

        .whatsapp-float:hover {
            background-color: #1ebe5d;
            transform: scale(1.1);
        }

        @media screen and (max-width: 576px) {
            .whatsapp-float {
                width: 50px;
                height: 50px;
                font-size: 24px;
                bottom: 15px;
                right: 15px;
            }
        }

        /* Modern Navbar Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            box-shadow: none !important;
        }
        
        .navbar .navbar-brand-wrapper {
            background: transparent !important;
            border-right: none !important;
        }

        .navbar .navbar-menu-wrapper {
            box-shadow: none !important;
        }

        .main-panel {
            background-color: #f8fafc;
        }

        .content-wrapper {
            background-color: transparent !important;
            padding: 2.5rem 2rem !important;
        }

        /* Global Theme Overrides */
        :root {
            --primary-blue: #2563eb;
            --primary-blue-hover: #1d4ed8;
        }
        .text-primary, .text-info {
            color: var(--primary-blue) !important;
        }
        .btn-primary, .btn-info {
            background-color: var(--primary-blue) !important;
            border-color: var(--primary-blue) !important;
            color: #fff !important;
        }
        .btn-primary:hover, .btn-info:hover {
            background-color: var(--primary-blue-hover) !important;
            border-color: var(--primary-blue-hover) !important;
        }
        .btn-outline-primary, .btn-outline-info {
            color: var(--primary-blue) !important;
            border-color: var(--primary-blue) !important;
        }
        .btn-outline-primary:hover, .btn-outline-info:hover {
            background-color: var(--primary-blue) !important;
            color: #fff !important;
        }
        .badge-primary, .bg-primary {
            background-color: var(--primary-blue) !important;
        }

        /* Branded Loader */
        #loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            transition: opacity 0.5s ease, visibility 0.5s;
        }
        .loader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5rem;
        }
        .loader-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loader-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: -0.025em;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div id="loader-wrapper">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-logo">Ningood</div>
        </div>
    </div>
    <div class="container-scroller">
        @include('partials.navbar')
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            @include('partials.sidebar')

            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">

                    @yield('content')

                </div>
                <!-- content-wrapper ends -->

                @include('partials.footer')
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>

    <!-- plugins:js -->
    <script src="{{ asset('assets/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->

    <!-- Plugin js for this page -->
    <script src="{{ asset('assets/js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap-datepicker.min.js') }}"></script>
    <!-- End plugin js for this page -->

    <!-- inject:js -->
    <script src="{{ asset('assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
    <script src="{{ asset('assets/js/template.js') }}"></script>
    <script src="{{ asset('assets/js/settings.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader-wrapper');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.visibility = 'hidden';
                }, 500);
            }
        });
    </script>
    <script>
        function toggleSubmenu(e, id) {
            e.preventDefault();
            const submenu = document.getElementById(id);
            submenu.classList.toggle('show');
        }
    </script>
    <!-- endinject -->

    <!-- Custom js for this page-->

    @stack('scripts')
    <a href="{{ route('user.support') }}" class="whatsapp-float pulse" target="_blank">
        <i class="bi bi-whatsapp"></i>
    </a>
</body>

</html>
