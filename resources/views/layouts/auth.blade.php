<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', config('app.name'))</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                    }
                }
            }
        }
    </script>
    @stack('styles')
    <style>
        body { font-family: 'Inter', sans-serif; }
        .mesh-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffff;
            background-image: 
                radial-gradient(at 0% 0%, hsla(220,100%,95%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(200,100%,98%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(220,100%,95%,1) 0, transparent 50%), 
                radial-gradient(at 50% 100%, hsla(220,100%,98%,1) 0, transparent 50%);
            z-index: -2;
        }
        .mesh-gradient::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.6' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.03;
            z-index: -1;
        }
        .floating-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
            animation: float 20s infinite alternate;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 50px) scale(1.1); }
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
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

<body class="bg-white text-slate-900 antialiased min-h-screen flex flex-col relative overflow-hidden">
    <!-- Preloader -->
    <div id="loader-wrapper">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-logo">Ningood</div>
        </div>
    </div>
    <!-- Mesh Background Layers -->
    <div class="mesh-gradient"></div>
    <div class="floating-shape bg-primary-200 w-96 h-96 -top-20 -left-20"></div>
    <div class="floating-shape bg-blue-100 w-80 h-80 top-1/2 -right-20"></div>
    <div class="floating-shape bg-indigo-50 w-72 h-72 bottom-0 left-1/4"></div>

    <!-- Main Content Area -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="p-6 text-center text-sm text-slate-400">
        &copy; {{ date('Y') }} Ningood. All rights reserved.
    </footer>

    @stack('scripts')
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
</body>

</html>
