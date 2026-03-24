@extends('layouts.dashboard')

@section('title', 'Dashboard')
@push('styles')
    <style>
        .service-card {
            border-radius: 16px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .service-icon img {
            border-radius: 8px;
        }

        .service-card {
            border-radius: 20px;
            /* Almost circular */
        }


        .service-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .service-icon img {
            max-width: 40px;
            max-height: 40px;
            object-fit: contain;
            transition: transform 0.3s ease;
            filter: grayscale(20%);
        }

        .service-card:hover .service-icon img {
            transform: scale(1.1);
            filter: none;
        }

        h6 {
            font-size: 0.95rem;
        }

        @media (max-width: 576px) {
            .service-icon img {
                max-width: 34px;
                max-height: 34px;
            }

            h6 {
                font-size: 0.85rem;
            }
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #28a745, #218838);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }

        .bg-gradient-dark {
            background: linear-gradient(135deg, #343a40, #23272b);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
        }

        /* Default style (for larger screens) */
        .price {
            font-size: 2rem;
            /* Default font size for larger screens */
            white-space: normal;
            /* Allow wrapping on larger screens */
            overflow: visible;
            /* Allow content to overflow if necessary */
            text-overflow: unset;
            /* Reset ellipsis */
            line-height: 1.2;
            /* Standard line height */
        }

        /* Style for smaller screens (e.g., mobile or tablet) */
        @media (max-width: 767px) {
            .price {
                font-size: 1.2rem;
                /* Adjust font size for smaller screens */
                white-space: nowrap;
                /* Prevent text from wrapping */
                overflow: hidden;
                /* Hide overflow */
                text-overflow: ellipsis;
                /* Show ellipsis if text overflows */
            }
        }

        @media (max-width: 1366px) and (max-height: 635px) {
            .price {
                font-size: 1.2rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        /* Default style (for larger screens) */
        .price {
            font-size: 2rem;
            /* Default font size for larger screens */
            white-space: normal;
            /* Allow wrapping on larger screens */
            overflow: visible;
            /* Allow content to overflow if necessary */
            text-overflow: unset;
            /* Reset ellipsis */
            line-height: 1.2;
            /* Standard line height */
        }

        /* Style for smaller screens (e.g., mobile or tablet) */
        @media (max-width: 767px) {
            .price {
                font-size: 1.2rem;
                /* Adjust font size for smaller screens */
                white-space: nowrap;
                /* Prevent text from wrapping */
                overflow: hidden;
                /* Hide overflow */
                text-overflow: ellipsis;
                /* Show ellipsis if text overflows */
            }
        }

        /* General Styles for Service Cards */
        .service-card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .icon-box {
            margin-bottom: 1.5rem;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        /* Modern Icon Circle */
        .icon-circle {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f1f5f9;
            /* Soft light background */
            color: #2563eb;
            /* Primary blue icon */
            border-radius: 16px;
            width: 60px;
            height: 60px;
            font-size: 24px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .card:hover .icon-circle {
            background-color: #2563eb;
            color: #ffffff;
            border-radius: 50%;
        }

        .icon-box-title {
            font-weight: bolder;
            font-size: 1rem;
            color: #333;
        }

        /* Responsive Layout */
        @media (max-width: 768px) {
            .icon-box-media {
                width: 60px;
                height: 60px;
            }

            .icon-box-title {
                font-size: 1rem;
            }
        }

        /* Ensures 2 items per row on mobile (smaller than 576px) */
        @media (max-width: 576px) {
            .col-6 {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .icon-box-media {
                width: 50px;
                height: 50px;
            }

            .icon-box-title {
                font-size: 0.9rem;
            }
        }

        /* Custom CSS for icon box */
        .icon-box-media {
            transition: transform 0.3s ease;
        }

        .icon-box-media:hover {
            transform: scale(1.1);
        }

        .primary-btn {
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
            background-color: #2563eb;
        }

        .primary-btn:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
        }

        /* Base Styles */
        .services-container {
            width: 100%;
            padding: 0 15px;
        }

        .services-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem 0;
        }

        .services-heading {
            font-weight: 300;
            margin-bottom: 2rem;
            text-align: center;
            color: #333;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .service-card-container {
            position: relative;
        }

        .service-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            height: 100%;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .service-icon-wrapper {
            margin-bottom: 1rem;
        }

        .service-icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .service-icon {
            color: white;
            font-size: 1.75rem;
        }

        .service-title {
            font-weight: 600;
            margin: 0;
            color: #333;
            font-size: 0.9rem;
        }

        .service-link {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
        }

        /* Color Classes */
        .bg-primary {
            background-color: #2563eb;
        }

        .bg-secondary {
            background-color: #6c757d;
        }

        /* Responsive Breakpoints */
        @media (min-width: 576px) {
            .services-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.25rem;
            }

            .service-icon-circle {
                width: 70px;
                height: 70px;
            }

            .service-icon {
                font-size: 2rem;
            }

            .service-title {
                font-size: 1rem;
            }
        }

        @media (min-width: 768px) {
            .services-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.5rem;
            }
        }

        @media (min-width: 992px) {
            .services-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .service-icon-circle {
                width: 80px;
                height: 80px;
            }

            .service-icon {
                font-size: 2.25rem;
            }

            .service-title {
                font-size: 1.1rem;
            }
        }

        @media (min-width: 1200px) {
            .services-grid {
                grid-template-columns: repeat(6, 1fr);
            }
        }

        /* Metric Card */
        .metric-card {
            border-radius: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        /* Icon wrapper */
        .metric-icon {
            font-size: clamp(1.5rem, 3vw, 2rem);
            margin-right: 0.75rem;
            flex-shrink: 0;
        }

        /* Title */
        .metric-title {
            font-size: clamp(0.8rem, 1vw, 1rem);
            font-weight: 600;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .metric-value {
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
            text-align: right;

            /* responsive font size that shrinks for long numbers */
            font-size: 2rem;
            /* default */
            max-width: 100%;
            /* prevent overflow */
        }

        @media (max-width: 768px) {
            .metric-value {
                font-size: 1.5rem;
                /* smaller on tablets */
            }
        }

        @media (max-width: 576px) {
            .metric-value {
                font-size: 1.2rem;
                /* smaller on phones */
            }
        }




        /* Small screen tweaks */
        @media (max-width: 576px) {
            .metric-card {
                min-height: 100px;
                padding: 0.75rem;
            }

            .metric-icon {
                font-size: 1.4rem;
            }

            .metric-value {
                font-size: 1.25rem;
            }
        }
    </style>
@endpush
@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
            <p class="mb-0">Here’s a quick look at your dashboard.</p>
        </div>
        @if ($status == 'Pending')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                We're excited to have you on board! However, we need to verify your identity before activating your
                account. Simply click the link below to complete the verification process<br>
            </div>
        @endif
        @include('common.message')
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-md-6 col-6 grid-margin stretch-card">
                    <div class="card hover-shadow">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="mdi mdi-wallet-outline mdi-36px"></i>
                                <p class="fw-medium mt-3">Main Wallet</p>
                            </div>
                            <h1 class="fw-light price">
                                ₦{{ auth()->user()->wallet ? number_format(auth()->user()->wallet->balance, 2) : '0.00' }}
                            </h1>

                            <a href="#" data-bs-toggle="modal" data-bs-target="#walletModal2"
                                class="btn btn-sm btn-outline-primary mt-3">
                                Add Fund
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-6 grid-margin stretch-card">
                    <div class="card hover-shadow">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="mdi mdi-gift-outline mdi-36px"></i>
                                <p class="fw-medium mt-3">Bonus Wallet</p>
                            </div>
                            <h1 class="fw-light price">
                                ₦{{ auth()->user()->wallet ? number_format(auth()->user()->wallet->bonus, 2) : '0.00' }}
                            </h1>

                            <a href="{{ route('user.wallet') }}" class="btn btn-sm btn-outline-danger mt-3">
                                Claim Bonus
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @if (auth()->user()->role == 'admin')
                <div class="row g-3 g-sm-4 mb-4">
                    @foreach ($metrics as $metric)
                        <div class="col-6 col-sm-4 col-md-3">
                            <x-dashboard.metric :title="$metric['title']" :value="$metric['value']" :icon="$metric['icon']" :bg="$metric['bg']"
                                :href="$metric['href']" />
                        </div>
                    @endforeach
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card mb-2">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Daily Charts</h5>
                            </div>
                            <div class="card-body">
                                <div style="max-height: 300px;">
                                    <canvas id="depositBreakdownChart" style="height: 100%; max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Top Funding</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="fundingChart" width="600" height="400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="row">
                <div class="services-container">
                    <div class="services-wrapper">
                        <h4 class="services-heading">Our Services</h4>
                        <div class="services-grid">
                            <!-- Single NIN Verification Card with Modal -->
                            <div class="service-card-container">
                                <div class="service-card" onclick="showNinModal()" style="cursor: pointer;">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-fingerprint service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Verify NIN</h5>
                                    <div class="service-link"></div>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-patch-check service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Generate TIN</h5>
                                    <a href="{{ route('user.verify-tin') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-fingerprint service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Self Service Delink</h5>
                                    <a href="{{ route('user.nin.delink') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card" onclick="showNinPhoneModal()" style="cursor: pointer;">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-phone service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Verify NIN Phone</h5>
                                    <div class="service-link"></div>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card" onclick="showNinDemoModal()" style="cursor: pointer;">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-fingerprint service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Verify NIN Demographic</h5>
                                    <div class="service-link"></div>
                                </div>
                            </div>


                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-search service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">IPE</h5>
                                    <a href="{{ route('user.ipe.v3') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-credit-card service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Verify BVN</h5>
                                    <a href="{{ route('user.verify-bvn') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-phone-vibrate service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">BVN Phone Search</h5>
                                    <a href="{{ route('user.bvn-phone-search') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-person-badge service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Personalize</h5>
                                    <a href="{{ route('user.personalize-nin') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-person-plus service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">BVN User Request</h5>
                                    <a href="{{ route('user.bvn-enrollment') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-person-badge service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">NIN Validation</h5>
                                    <a href="{{ route('user.nin.services') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-pencil-square service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">NIN Modification</h5>
                                    <a href="{{ route('user.nin.mod') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-pencil-square service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">BVN Modification</h5>
                                    <a href="{{ route('user.bank-services.index') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-pencil-square service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Mod IPE Clearance</h5>
                                    <a href="{{ route('user.nin.mod.ipe') }}" class="service-link"></a>
                                </div>
                            </div>
                            <div class="service-card-container position-relative">
                                <div class="service-card position-relative">

                                    <span
                                        class="badge bg-danger position-absolute top-50 start-0 translate-middle-y px-2 py-1"
                                        style="font-size:0.75rem;">
                                        NEW
                                    </span>

                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-pencil-square service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Business Name (CAC)</h5>
                                    <a href="{{ route('user.business.create') }}" class="service-link"></a>
                                </div>
                            </div>

                            <div class="service-card-container position-relative">
                                <div class="service-card position-relative">
                                    <span
                                        class="badge bg-danger position-absolute top-50 start-0 translate-middle-y px-2 py-1"
                                        style="font-size:0.75rem;">
                                        NEW
                                    </span>
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-primary">
                                            <i class="bi bi-building service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Company Reg (CAC)</h5>
                                    <a href="{{ route('user.company.create') }}" class="service-link"></a>
                                </div>
                            </div>




                            <div class="service-card-container">
                                <div class="service-card">
                                    <div class="service-icon-wrapper">
                                        <div class="service-icon-circle bg-secondary">
                                            <i class="bi bi-plus-lg service-icon"></i>
                                        </div>
                                    </div>
                                    <h5 class="service-title">Coming Soon</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="ninModal" tabindex="-1" aria-labelledby="ninModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ninModalLabel">Select NIN Verification Type</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <a href="{{ route('user.verify-nin') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">Verify NIN V1</h6>
                                                    <small class="text-success">Charged only upon successful
                                                        verification</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-12">
                                        <a href="{{ route('user.verify-nin2') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">Verify NIN V2</h6>
                                                    <small class="text-success">Charged only upon successful
                                                        verification</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-12">
                                        <a href="{{ route('user.verify-nin4') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">Verify NIN V3</h6>
                                                    <small class="text-danger">NIN V3 charges apply even if verification
                                                        fails</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-12">
                                        <a href="{{ route('user.verify-nin5') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">Verify NIN V4</h6>
                                                    <small class="text-success">Charged only upon successful
                                                        verification, better for NIN Modifications</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="col-12">
                                        <a href="{{ route('user.verify-nin6') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">Verify NIN V5</h6>
                                                    <small class="text-success">Charged only upon successful
                                                        verification</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="ninPhoneModal" tabindex="-1" aria-labelledby="ninPhoneModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="ninPhoneModalLabel">
                                    Select NIN Phone Verification Type
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">

                                    <!-- NIN Phone V1 -->
                                    <div class="col-12">
                                        <a href="{{ route('user.verify-nin-phone') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-phone service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">
                                                        Verify NIN Phone V1
                                                    </h6>
                                                    <small class="text-success">
                                                        Charged only upon successful verification
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- NIN Phone V2 -->
                                    <div class="col-12">
                                        <a href="{{ route('user.verify-phone-v5') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-phone service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">
                                                        Verify NIN Phone V2
                                                    </h6>
                                                    <small class="text-success">
                                                        Charged only upon successful verification
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="ninDemoModal" tabindex="-1" aria-labelledby="ninDemoModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title" id="ninDemoModalLabel">
                                    Select NIN Demographic Verification Type
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <div class="modal-body">
                                <div class="row g-3">

                                    <!-- Demographic V1 -->
                                    <div class="col-12">
                                        <a href="{{ route('user.verify-demo') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">
                                                        Verify Demographic V1
                                                    </h6>
                                                    <small class="text-success">
                                                        Charged only upon successful verification
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <!-- Demographic V2 -->
                                    <div class="col-12">
                                        <a href="{{ route('user.verify-demo-v5') }}" class="text-decoration-none">
                                            <div class="card service-option-card text-center h-100 border-0">
                                                <div
                                                    class="card-body d-flex flex-column align-items-center justify-content-center p-3">
                                                    <div class="service-icon-wrapper mb-2">
                                                        <div class="service-icon-circle bg-primary">
                                                            <i class="bi bi-fingerprint service-icon"></i>
                                                        </div>
                                                    </div>
                                                    <h6 class="fw-semibold text-dark mb-1">
                                                        Verify Demographic V2
                                                    </h6>
                                                    <small class="text-success">
                                                        Charged only upon successful verification
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>



                <div class="col-lg-12 stretch-card mt-">
                    <div class="container py-3" style="max-width: 100%">
                        <h4 class="fw-light mb-4 text-center">Recent Transactions</h4>
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="table-responsive">
                                    @php
                                        $transactions = auth()->user()->transactions()->latest()->paginate(10);
                                        $serialNumber =
                                            ($transactions->currentPage() - 1) * $transactions->perPage() + 1;
                                    @endphp

                                    @forelse ($transactions as $data)
                                        @if ($loop->first)
                                            <table class="table text-nowrap" style="background: #fafafc !important;">
                                                <thead>
                                                    <tr class="table-primary">
                                                        <th width="5%">ID</th>
                                                        <th>Reference No.</th>
                                                        <th>Service Type</th>
                                                        <th>Description</th>
                                                        <th>Amount</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Receipt</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                        @endif

                                        <tr>
                                            <td>{{ $serialNumber++ }}</td>
                                            <td>
                                                <a target="_blank"
                                                    href="{{ route('user.reciept', $data->referenceId) }}">
                                                    {{ strtoupper($data->referenceId) }}
                                                </a>
                                            </td>
                                            <td>{{ $data->service_type }}</td>
                                            <td>{{ $data->service_description }}</td>
                                            <td>&#8358;{{ number_format($data->amount, 2) }}</td>
                                            <td class="text-center">
                                                <span
                                                    class="badge
                                                    {{ $data->status == 'Approved' ? 'bg-success' : ($data->status == 'Rejected' ? 'bg-danger' : 'bg-warning') }}">
                                                    {{ strtoupper($data->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a target="_blank" href="{{ route('user.reciept', $data->referenceId) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>

                                        @if ($loop->last)
                                            </tbody>
                                            </table>

                                            <div class="d-flex justify-content-center mt-3">
                                                {{ $transactions->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                                            </div>
                                        @endif
                                    @empty
                                        <div class="text-center">
                                            <p class="fw-semibold fs-15 mt-2">No Transaction Available!</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="kycModal" tabindex="-1" aria-labelledby="kycModal" data-bs-keyboard="true"
                data-bs-backdrop="static" data-bs-keyboard="false">

                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="staticBackdropLabel2">Verify Account
                            </h6>
                        </div>
                        <div class="modal-body">
                            We're excited to have you on board! However, we need to verify your identity before activating
                            your
                            account. provide your Identification number below.
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="col-md-6 col-lg-6">
                                <form id="verify" name="verifyForm" method="POST"
                                    action="{{ route('user.verify-user') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <p class="mb-2 text-muted text-center">Enter your BVN No.</p>
                                        <input type="text" id="bvn" name="bvn"
                                            class="form-control text-center" maxlength="11" required />
                                    </div>
                                    <div class="text-center mb-3 d-flex justify-content-center gap-2">
                                        <button type="submit" id="submit" class="btn btn-primary">
                                            <i class="lar la-check-circle"></i> Verify Now
                                        </button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('logout') }}" class="text-center mb-3">
                                    @csrf
                                    <button type="submit" class="btn btn-danger">
                                        <i class="las la-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- <div class="modal fade" id="walletModal" tabindex="-1" aria-labelledby="walletModalModalLabel"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="walletModalLabel">Fund Wallet</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <small class="fw-semibold">Fund your wallet instantly by depositing
                                into the virtual account number</small>
                            <ul class="list-unstyled virtual-account-list mt-3 mb-0">
                                @if (auth()->user()->virtualAccount != null)
                                    @foreach (auth()->user()->virtualAccount as $data)
                                        <li class="account-item mb-3 p-2">
                                            <div class="d-flex align-items-start">
                                                <div class="bank-logo me-3">
                                                    <img src="{{ asset('assets/images/' . strtolower(str_replace(' ', '', $data->bankName)) . '.png') }}"
                                                        alt="{{ $data->bankName }} logo">
                                                </div>
                                                <div class="flex-fill">
                                                    <p class="account-name mb-1">{{ $data->accountName }}</p>
                                                    <span class="account-number d-block">{{ $data->accountNo }}</span>
                                                    <small class="bank-name text-muted">{{ $data->bankName }}</small>
                                                </div>
                                                <div class="copy-btn-wrap ms-auto">
                                                    <button class="btn btn-outline-secondary btn-sm copy-account-number"
                                                        data-account="{{ $data->accountNo }}">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <hr>
                            <center>
                                <a style="text-decoration:none" class="mb-2" href="{{ route('user.support') }}">
                                    <small class="fw-semibol text-danger">If your funds is not
                                        received within 30mins.
                                        Please Contact Support
                                        <i class="mdi mdi-headphones mdi-12px" style="font-size:24px"></i>
                                    </small> </a>

                                <a style="text-decoration:none" href="{{ route('user.wallet') }}">
                                    <h4 class="fw-semibol text-danger">Go to wallet
                                        <i class="mdi mdi-wallet-outline mdi-36px" style="font-size:24px"></i>
                                    </h4>
                                </a>
                            </center>

                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Virtual Account Modal -->
            <div class="modal fade" id="walletModal2" tabindex="-1" aria-labelledby="walletModalModalLabel2"
                aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="walletModalLabel">Fund Wallet</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @if (auth()->user()->virtualAccount && auth()->user()->virtualAccount->isNotEmpty())
                                <p class="text-muted mb-3 text-center">
                                    Use any of the virtual accounts below to fund your wallet.
                                </p>

                                <ul class="list-unstyled virtual-account-list mt-3 mb-0">
                                    @foreach (auth()->user()->virtualAccount as $data)
                                        <li class="account-item mb-3 p-2 border rounded bg-white shadow-sm">
                                            <div class="d-flex align-items-start">
                                                <div class="bank-logo me-3">
                                                    <img src="{{ asset('assets/images/' . strtolower(str_replace(' ', '', $data->bankName)) . '.png') }}"
                                                        alt="{{ $data->bankName }} logo" width="40" height="40"
                                                        class="rounded">
                                                </div>
                                                <div class="flex-fill">
                                                    <p class="account-name mb-1 fw-bold">{{ $data->accountName }}</p>
                                                    <span class="account-number d-block">{{ $data->accountNo }}</span>
                                                    <small class="bank-name text-muted">{{ $data->bankName }}</small>
                                                </div>
                                                <div class="copy-btn-wrap ms-auto">
                                                    <button type="button"
                                                        class="btn btn-outline-secondary btn-sm copy-account-number"
                                                        data-account="{{ $data->accountNo }}">
                                                        Copy
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <h6 class="mb-3 text-muted">You don’t have a virtual account yet.</h6>
                                <p class="text-danger">
                                    To generate a virtual account, please enter your BVN below.
                                </p>


                                <form id="verify" name="verifyForm" method="POST"
                                    action="{{ route('user.verify-user') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <p class="mb-2 text-muted text-center">Enter your BVN No.</p>
                                        <input type="text" id="bvn" name="bvn"
                                            class="form-control text-center" maxlength="11" required />
                                    </div>
                                    <div class="text-center mb-3 d-flex justify-content-center gap-2">
                                        <button type="submit" id="submit" class="btn btn-primary">
                                            <i class="lar la-check-circle"></i> Verify Now
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        @endsection
        @push('scripts')
            <script>
                function showNinModal() {
                    const ninModal = new bootstrap.Modal(document.getElementById('ninModal'));
                    ninModal.show();
                }

                function showNinPhoneModal() {
                    const ninPhoneModal = new bootstrap.Modal(document.getElementById('ninPhoneModal'));
                    ninPhoneModal.show();
                }

                function showNinDemoModal() {
                    const ninDemoModal = new bootstrap.Modal(document.getElementById('ninDemoModal'));
                    ninDemoModal.show();
                }

                @if ($kycPending)
                    const kycModal = new bootstrap.Modal(document.getElementById('kycModal'));
                    kycModal.show();
                @endif

                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.getElementById('verify');
                    const submitButton = document.getElementById('submit');

                    if (form && submitButton) {
                        form.addEventListener('submit', function() {
                            submitButton.disabled = true;
                            submitButton.innerText = 'Verifying ...';
                        });
                    }
                });


                document.querySelectorAll('.copy-account-number').forEach(button => {
                    button.addEventListener('click', function() {
                        const acctNo = this.getAttribute('data-account');
                        navigator.clipboard.writeText(acctNo);
                        this.innerText = 'Copied!';
                        setTimeout(() => {
                            this.innerText = 'Copy';
                        }, 2000);
                    });
                });
            </script>
            @if (!empty($popup))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        let popupDiv = document.createElement('div');
                        popupDiv.innerHTML = `
            <div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="popupModalLabel"> {{ $popup->title ?? 'Notice' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    {!! nl2br(e(str_replace('{name}', auth()->user()->name, $popup->message))) !!}
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>
        `;
                        document.body.appendChild(popupDiv);
                        let modal = new bootstrap.Modal(document.getElementById('popupModal'));
                        modal.show();
                    });
                </script>
            @endif

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const data = @json($depositChartData);
                    const labels = Object.keys(data);
                    const values = Object.values(data);

                    const ctx = document.getElementById('depositBreakdownChart');
                    if (ctx) {
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Deposits Breakdown',
                                    data: values,
                                    backgroundColor: [
                                        'rgba(25, 135, 84, 0.7)',
                                        'rgba(255, 193, 7, 0.7)',
                                        'rgba(220, 53, 69, 0.7)'
                                    ],
                                    borderColor: [
                                        'rgba(25, 135, 84, 1)',
                                        'rgba(255, 193, 7, 1)',
                                        'rgba(220, 53, 69, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: context =>
                                                `${context.label}: ₦${context.parsed.toLocaleString()}`
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
            <script>
                const ctx = document.getElementById('fundingChart').getContext('2d');

                const data = {
                    labels: @json($topFunders->pluck('name')), // user names
                    datasets: [{
                        label: 'Top 5 Funders Today',
                        data: @json($topFunders->pluck('total_funding')), // funding amounts
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: '#fff',
                        borderWidth: 1,
                    }]
                };

                new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let index = context.dataIndex;
                                        let email = @json($topFunders->pluck('email'))[index];
                                        let amount = context.formattedValue;
                                        return email + ': ₦' + amount;
                                    }
                                }
                            },
                            title: {
                                display: true,
                                text: 'Top 5 Funders for Today'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₦' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }

                });
            </script>
        @endpush
