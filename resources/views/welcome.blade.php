<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ningood - Verify Identities with Speed & Precision</title>
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
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 50%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-blob {
            position: absolute;
            top: 0;
            right: 0;
            width: 50%;
            height: 100%;
            background: radial-gradient(circle at 70% 30%, #eff6ff 0%, transparent 70%);
            z-index: -1;
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
        .hidden { opacity: 0; pointer-events: none; }
    </style>
</head>

<body class="bg-white text-slate-900 antialiased overflow-x-hidden">
    <!-- Preloader -->
    <div id="loader-wrapper">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <div class="loader-logo">Ningood</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sticky top-0 bg-white/80 backdrop-blur-md z-50 border-b border-gray-50">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary-200">
                    <i class="bi bi-shield-check text-xl"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight">Ningood</span>
            </div>
            
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#" class="hover:text-primary-600 transition">Features</a>
                <a href="#services" class="hover:text-primary-600 transition">Services</a>
                <a href="#contact" class="hover:text-primary-600 transition">Contact</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('auth.login') }}" class="text-sm font-bold text-slate-700 hover:text-primary-600 transition">Log in</a>
                <a href="{{ route('auth.register') }}" class="px-6 py-2.5 bg-primary-600 text-white text-sm font-bold rounded-full hover:bg-primary-700 shadow-lg shadow-primary-200 transition">Get Started</a>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero Section -->
        <section class="relative pt-20 pb-32">
            <div class="hero-blob"></div>
            <div class="max-w-7xl mx-auto px-6 text-center">
                <!-- Status Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-50 text-green-700 text-xs font-bold rounded-full border border-green-100 mb-10">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Live Verification System
                </div>

                <!-- Headline -->
                <h1 class="text-5xl md:text-7xl font-extrabold text-slate-900 mb-8 tracking-tight">
                    Verify Identities with<br/>
                    <span class="gradient-text">Speed & Precision</span>
                </h1>

                <!-- Subheadline -->
                <p class="max-w-2xl mx-auto text-lg text-slate-500 mb-12 leading-relaxed">
                    The most reliable platform for NIN, BVN, and demographic verification.<br class="hidden md:block"/>
                    Simple, secure, and built for modern businesses.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-center gap-4 mb-20">
                    <a href="{{ route('auth.register') }}" class="px-10 py-4 bg-primary-600 text-white font-bold rounded-full shadow-2xl shadow-primary-200 hover:bg-primary-700 transition transform hover:-translate-y-1">
                        Get Started Now
                    </a>
                    <a href="#services" class="px-10 py-4 bg-white text-slate-700 font-bold rounded-full border border-slate-200 hover:bg-slate-50 transition transform hover:-translate-y-1">
                        View Services
                    </a>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-4xl mx-auto pt-10">
                    <div class="text-center">
                        <div class="text-4xl font-extrabold text-slate-900 mb-1">99.9%</div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-widest">Uptime</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-extrabold text-slate-900 mb-1">2s</div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-widest">Avg. Response</div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl font-extrabold text-slate-900 mb-1">10k+</div>
                        <div class="text-sm font-medium text-slate-400 uppercase tracking-widest">Verifications</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 px-6">
            <div class="max-w-6xl mx-auto bg-primary-900 rounded-[2.5rem] p-12 md:p-24 text-center relative overflow-hidden shadow-3xl">
                <!-- Decorative Circle -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-primary-800 rounded-full opacity-40"></div>
                <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-primary-800 rounded-full opacity-40"></div>
                
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-8 relative z-10">Ready to scale your verification?</h2>
                <p class="text-primary-100 text-lg md:text-xl mb-12 max-w-2xl mx-auto relative z-10 leading-relaxed font-medium">
                    Join thousands of businesses trusting Ningood for their identity verification needs.
                </p>
                <div class="relative z-10">
                    <a href="{{ route('auth.register') }}" class="px-12 py-5 bg-white text-primary-900 font-bold rounded-full hover:bg-slate-50 transition-all shadow-xl hover:shadow-white/10 active:scale-95">
                        Create Free Account
                    </a>
                </div>
            </div>
        </section>

        <!-- Services Section Preview -->
        <section id="services" class="py-24 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <h2 class="text-3xl font-bold mb-16">Comprehensive Verification Services</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    @php
                        $previewServices = [
                            ['icon' => 'bi-fingerprint', 'title' => 'NIN Verification'],
                            ['icon' => 'bi-credit-card-2-front', 'title' => 'BVN Search'],
                            ['icon' => 'bi-building-check', 'title' => 'CAC Services'],
                            ['icon' => 'bi-person-badge', 'title' => 'IPE Clearance']
                        ];
                    @endphp
                    @foreach($previewServices as $s)
                    <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl transition-all group">
                        <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 text-2xl mb-6 group-hover:bg-primary-600 group-hover:text-white transition-all">
                            <i class="bi {{ $s['icon'] }}"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2">{{ $s['title'] }}</h3>
                        <p class="text-slate-500 text-sm">Secure and fast processing with instant results.</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="py-24">
            <div class="max-w-3xl mx-auto px-6 bg-white rounded-3xl border border-slate-100 shadow-2xl p-10 md:p-16">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold mb-4">Get in Touch</h2>
                    <p class="text-slate-500 italic">Have questions? We're here to help you get started.</p>
                </div>
                
                <form action="#" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Name</label>
                            <input type="text" placeholder="John Doe" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                            <input type="email" placeholder="john@example.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Message</label>
                        <textarea rows="4" placeholder="How can we help?" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 transition"></textarea>
                    </div>
                    <button class="w-full py-4 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 shadow-lg shadow-primary-200 transition">
                        Send Message
                    </button>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 py-16 text-slate-400">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center text-white">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight">Ningood</span>
                    </div>
                    <p class="text-sm leading-relaxed mb-6">
                        Leading identity verification solutions for Nigeria's growing digital economy.
                    </p>
                    <div class="flex gap-4">
                        <i class="bi bi-facebook text-xl hover:text-white transition cursor-pointer"></i>
                        <i class="bi bi-twitter-x text-xl hover:text-white transition cursor-pointer"></i>
                        <i class="bi bi-whatsapp text-xl hover:text-white transition cursor-pointer"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Services</h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="#" class="hover:text-white transition">NIN Verification</a></li>
                        <li><a href="#" class="hover:text-white transition">BVN Search</a></li>
                        <li><a href="#" class="hover:text-white transition">CAC Registration</a></li>
                        <li><a href="#" class="hover:text-white transition">TIN Generation</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Company</h4>
                    <ul class="space-y-4 text-sm">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition">Support</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-6">Support</h4>
                    <p class="text-sm leading-relaxed">
                        Need help? Our team is available to assist you 24/7.
                    </p>
                    <a href="mailto:support@ningood.ng" class="block mt-4 text-primary-500 font-bold">support@ningood.ng</a>
                </div>
            </div>
            <div class="pt-8 border-t border-slate-800 text-center text-xs">
                &copy; {{ date('Y') }} Ningood. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        window.onload = function() {
            setTimeout(() => {
                document.getElementById('loader-wrapper').classList.add('hidden');
            }, 500);
        };
    </script>
</body>
</html>
