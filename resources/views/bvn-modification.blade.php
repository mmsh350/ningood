@extends('layouts.dashboard')

@section('title', 'BVN Modification')
@push('styles')
    <style>
        .pagination .page-link {
            min-width: 36px;
            text-align: center;
        }

        @media (max-width: 576px) {
            .pagination {
                font-size: 0.75rem;
            }
        }

        .block {
            display: block;
        }

        /* Improved table responsiveness */
        .table-responsive {
            margin-top: 1rem;
            overflow-x: auto;
        }

        .pagination {
            justify-content: center;
        }

        /* Ensure table is readable on mobile */
        @media (max-width: 768px) {

            .table th,
            .table td {
                padding: 0.5rem;
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.775rem;
            }
        }

        .mobile-card {
            border-radius: 12px;
            border: 1px solid #edf2f7;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .mobile-card:active {
            transform: scale(0.98);
        }

        /* Fix alignment for stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="mb-3 mt-1">
                <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
            </div>



            <!-- Main Content Card -->
            <div class="card">
                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="mb-2">
                                <h6 class="text-center text-uppercase text-muted fw-semibold mb-3"
                                    style="font-size: 0.85rem;">
                                    Total BVN Modification Requests
                                </h6>

                                <div class="stats-grid">
                                    @php
                                        $validationStats = [
                                            [
                                                'label' => 'All',
                                                'value' => $totalAll,
                                                'bg' => '#f8f9fa',
                                                'text' => 'text-dark',
                                                'border' => 'border',
                                            ],
                                            [
                                                'label' => 'Pending',
                                                'value' => $totalInProgress + $totalPending,
                                                'bg' => '#fff3cd',
                                                'text' => 'text-dark',
                                                'border' => 'border-warning',
                                            ],
                                            [
                                                'label' => 'Failed',
                                                'value' => $totalFailed,
                                                'bg' => '#f8d7da',
                                                'text' => 'text-danger',
                                                'border' => 'border-danger',
                                            ],
                                            [
                                                'label' => 'Successful',
                                                'value' => $totalSuccessful,
                                                'bg' => '#d1e7dd',
                                                'text' => 'text-success',
                                                'border' => 'border-success',
                                            ],
                                            [
                                                'label' => 'Queried',
                                                'value' => $totalQueried,
                                                'bg' => '#e2e3e5',
                                                'text' => 'text-muted',
                                                'border' => 'border-secondary',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($validationStats as $stat)
                                        <div class="border rounded-3 text-center py-2 px-1 shadow-sm {{ $stat['text'] }}"
                                            style="background: {{ $stat['bg'] }}; font-size: 0.85rem;">
                                            <div class="small fw-light mb-1">{{ $stat['label'] }}</div>
                                            <div class="fw-bold" style="font-size: 1.1rem;">{{ $stat['value'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Service Selection Section -->
                    <div class="row justify-content-center text-center mb-4">
                        <div class="col-12">
                            <center>
                                <img class="img-fluid rounded mb-2" src="{{ asset('assets/images/bvn.jpg') }}"
                                    width="30%">
                            </center>
                            <center>
                                <small class="font-italic text-danger mt-2">
                                    <i>Please note that this request will be processed within 10 - 12 working days. We
                                        appreciate your patience and will keep you updated on the status.</i>
                                </small>
                            </center>

                            <!-- Success Message -->
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Error Message -->
                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                    <strong>Whoops! There were some problems with your input:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row justify-content-center mt-3">
                                <div class="col-lg-12 col-md-12">
                                    <div class="card shadow-sm border-">
                                        <div class="card-body p-4">
                                            <h2 class="h5 fw-semibold mb-4 text-primary">Select Service</h2>

                                            <form id="serviceForm" method="POST"
                                                action="{{ route('user.modification-requests.action') }}"
                                                enctype="multipart/form-data">
                                                @csrf

                                                <!-- Bank Selection -->
                                                <div class="mb-3 text-start">
                                                    <label for="bank_id" class="form-label fw-semibold">Select Bank</label>
                                                    <select id="bank_id" name="bank_id" class="form-select text-dark"
                                                        required>
                                                        <option value="">Select a Bank</option>
                                                        @foreach ($banks as $bank)
                                                            <option class="text-dark" value="{{ $bank->id }}">
                                                                {{ $bank->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Service Selection -->
                                                <div class="mb-3 text-start">
                                                    <label for="service_id" class="form-label fw-semibold">Select
                                                        Service</label>
                                                    <select id="service_id" name="service_id" class="form-select text-dark"
                                                        disabled required>
                                                        <option value="" class="text-dark">Select a Service</option>
                                                    </select>
                                                </div>

                                                <!-- Modification Data -->
                                                <div id="modificationData" class="mb-4 text-start d-none">
                                                    <!-- BVN & NIN -->
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="bvn_no" class="form-label fw-semibold">BVN
                                                                Number<span class="text-danger">*</span></label>
                                                            <input type="text" id="bvn_no" name="bvn_no"
                                                                maxlength="11" class="form-control"
                                                                placeholder="Enter 11-digit BVN" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="nin_number" class="form-label fw-semibold">NIN
                                                                Number<span class="text-danger">*</span></label>
                                                            <input type="text" id="nin_number" name="nin_number"
                                                                maxlength="11" class="form-control"
                                                                placeholder="Enter 11-digit NIN" required>
                                                        </div>
                                                    </div>

                                                    <h5 class="fw-semibold text-dark mb-3">Details</h5>

                                                    <div class="row">
                                                        <!-- Current Details -->
                                                        <div class="col-md-6">
                                                            <h6 class="fw-medium text-muted mb-2">Current Details (Optional)
                                                            </h6>
                                                            <div class="mb-2">
                                                                <label class="form-label small">First Name</label>
                                                                <input type="text" name="current_firstname"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Middle Name</label>
                                                                <input type="text" name="current_middlename"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Surname</label>
                                                                <input type="text" name="current_surname"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Date of Birth</label>
                                                                <input type="date" name="current_dob"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Phone Number</label>
                                                                <input type="text" name="current_phone" maxlength="11"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Gender</label>
                                                                <select name="current_gender"
                                                                    class="form-select form-select-sm">
                                                                    <option value="">Select Gender</option>
                                                                    <option value="Male">Male</option>
                                                                    <option value="Female">Female</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Address</label>
                                                                <textarea name="current_address" class="form-control form-control-sm" rows="2"></textarea>
                                                            </div>
                                                        </div>

                                                        <!-- New Details -->
                                                        <div class="col-md-6">
                                                            <h6 class="fw-medium text-muted mb-2">New Details</h6>
                                                            <div class="mb-2">
                                                                <label class="form-label small">First Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="new_firstname"
                                                                    class="form-control form-control-sm" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Middle Name</label>
                                                                <input type="text" name="new_middlename"
                                                                    class="form-control form-control-sm">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Surname <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="new_surname"
                                                                    class="form-control form-control-sm" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Date of Birth <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="date" name="new_dob"
                                                                    class="form-control form-control-sm" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Phone Number <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" name="new_phone" maxlength="11"
                                                                    class="form-control form-control-sm" required>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Gender <span
                                                                        class="text-danger">*</span></label>
                                                                <select name="new_gender"
                                                                    class="form-select form-select-sm" required>
                                                                    <option value="">Select Gender</option>
                                                                    <option value="Male">Male</option>
                                                                    <option value="Female">Female</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label small">Address <span
                                                                        class="text-danger">*</span></label>
                                                                <textarea name="new_address" class="form-control form-control-sm" rows="2" required></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>


                                                <!-- Price Display -->
                                                <div id="priceDisplay" class="mb-3 d-none">
                                                    <div class="p-3 bg-light rounded border">
                                                        <hr class="my-2">
                                                        <div class="d-flex justify-content-between">
                                                            <span class="fw-semibold">Total:</span>
                                                            <span id="totalPrice"
                                                                class="fw-bold text-primary fs-5">₦0.00</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <button type="submit" id="submitBtn"
                                                    class="btn  text-light w-100 fw-semibold py-2"
                                                    style="background:#2563eb">
                                                    <i class="las la-share"></i> Submit Request
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="row g-2 mb-4">
                        <div class="col-md-8">
                            <form method="GET" action="{{ route('user.bank-services.index') }}">
                                <div class="input-group shadow-sm">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search by Ref No, BVN or NIN..." value="{{ request('search') }}">
                                    <button class="btn text-light" type="submit" style="background:#2563eb">
                                        <i class="las la-search me-1"></i> Search
                                    </button>
                                    @if (request('search') || request('status'))
                                        <a href="{{ route('user.bank-services.index') }}"
                                            class="btn btn-outline-secondary">
                                            <i class="las la-undo"></i>
                                        </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('user.bank-services.index') }}">
                                <select name="status" onchange="this.form.submit()"
                                    class="form-select shadow-sm text-dark">
                                    <option value="">Filter by Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                        Processing</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>
                                        Resolved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                    <option value="query" {{ request('status') == 'query' ? 'selected' : '' }}>Query
                                    </option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Desktop View (Large Screens) -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Ref No</th>
                                    <th>BVN</th>
                                    <th>Service</th>
                                    <th>Amount (₦)</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($modificationRequests as $index => $req)
                                    <tr>
                                        <td>{{ $modificationRequests->firstItem() + $index }}</td>
                                        <td>{{ $req->refno }}</td>
                                        <td>{{ $req->bvn_no }}</td>
                                        <td>{{ optional($req->service)->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($req->total_price, 2) }}</td>
                                        <td>
                                            @php
                                                $badgeClass = match ($req->status) {
                                                    'pending' => 'badge bg-warning',
                                                    'processing' => 'badge bg-info',
                                                    'resolved' => 'badge bg-success',
                                                    'rejected' => 'badge bg-danger',
                                                    'query' => 'badge bg-warning',
                                                    default => 'badge bg-secondary',
                                                };
                                            @endphp
                                            <span class="{{ $badgeClass }}">{{ ucfirst($req->status) }}</span>
                                        </td>
                                        <td>{{ $req->created_at->format('d M Y h:i A') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-dark me-1"
                                                data-bs-toggle="modal" data-bs-target="#detailsModal{{ $req->id }}">
                                                View
                                            </button>
                                            @if ($req->status === 'query')
                                                <a href="{{ route('user.bvn-modification.edit', $req->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No modification requests
                                            found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View (Small Screens) -->
                    <div class="d-md-none">
                        @forelse ($modificationRequests as $req)
                            @php
                                $badgeClass = match ($req->status) {
                                    'pending' => 'badge bg-warning',
                                    'processing' => 'badge bg-info',
                                    'resolved' => 'badge bg-success',
                                    'rejected' => 'badge bg-danger',
                                    'query' => 'badge bg-warning',
                                    default => 'badge bg-secondary',
                                };
                            @endphp
                            <div class="card mobile-card shadow-sm border-0 mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="small text-muted mb-1">
                                                <i class="las la-hashtag"></i> {{ $req->refno }}
                                            </div>
                                            <h6 class="mb-0 fw-bold text-dark">
                                                {{ optional($req->service)->name ?? 'N/A' }}</h6>
                                        </div>
                                        <span class="{{ $badgeClass }}">{{ ucfirst($req->status) }}</span>
                                    </div>

                                    <div class="bg-light rounded p-2 my-2 border-start border-primary border-3">
                                        <small class="text-muted d-block small">BVN Number</small>
                                        <span class="fw-medium">{{ $req->bvn_no }}</span>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <div>
                                            <small class="text-muted d-block">Amount</small>
                                            <span
                                                class="fw-bold text-primary">₦{{ number_format($req->total_price, 2) }}</span>
                                        </div>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-dark me-1"
                                                data-bs-toggle="modal" data-bs-target="#detailsModal{{ $req->id }}">
                                                <i class="las la-eye"></i> View
                                            </button>
                                            @if ($req->status === 'query')
                                                <a href="{{ route('user.bvn-modification.edit', $req->id) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="las la-edit"></i> Edit
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2 text-end">
                                        <small class="text-muted x-small" style="font-size: 0.75rem;">
                                            <i class="las la-calendar"></i> {{ $req->created_at->format('d M, Y h:i A') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="las la-folder-open fs-1 text-muted d-block mb-2"></i>
                                <p class="text-muted">No modification requests found.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3">
                        {{ $modificationRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach ($modificationRequests as $req)
        @php
            $data = $req->modification_data;
            $current = $data['current_data'] ?? [];
            $new = $data['new_data'] ?? [];
        @endphp

        <div class="modal fade" id="detailsModal{{ $req->id }}" tabindex="-1"
            aria-labelledby="detailsModalLabel{{ $req->id }}" aria-hidden="true" data-bs-backdrop="static"
            data-bs-keyboard="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="detailsModalLabel{{ $req->id }}">Request Details -
                            {{ $req->refno }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- BVN and NIN Information -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>BVN Number:</strong></p>
                                <p class="text-primary fw-bold">{{ $req->bvn_no }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>NIN Number:</strong></p>
                                <p class="text-primary fw-bold">{{ $req->nin_number }}</p>
                            </div>
                        </div>

                        <!-- Bank and Service -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Bank:</strong></p>
                                <p class="text-primary fw-bold">{{ optional($req->bank)->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Service:</strong></p>
                                <p class="text-primary fw-bold">{{ optional($req->service)->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <!-- Amount and Status -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Amount:</strong></p>
                                <p class="text-primary fw-bold">₦{{ number_format($req->total_price, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong></p>
                                @php
                                    $badgeClass = match ($req->status) {
                                        'pending' => 'badge bg-warning',
                                        'processing' => 'badge bg-info',
                                        'resolved' => 'badge bg-success',
                                        'rejected' => 'badge bg-danger',
                                        'query' => 'badge bg-warning',
                                        default => 'badge bg-secondary',
                                    };
                                @endphp
                                <span class="{{ $badgeClass }}">{{ ucfirst($req->status) }}</span>
                            </div>
                        </div>

                        <!-- Created Date -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <p><strong>Created Date:</strong></p>
                                <p class="text-muted">{{ $req->created_at->format('d M Y h:i A') }}</p>
                            </div>
                        </div>

                        <!-- Reason/Comments -->
                         <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="fw-bold mb-0" style="color: #2563eb;">
                                    <i class="las la-comment-dots me-1 fs-5"></i>
                                    Administrator Feedback
                                </h6>
                                <hr class="mt-2 mb-0" style="width: 50px; border-top: 2px solid #2563eb; opacity: 1;">
                                <p class="border rounded p-2 bg-light mt-2">
                                    {{ $req->reason ?? 'No additional information provided.' }}</p>
                            </div>
                        </div>

                        <hr>

                        <!-- Modification Data -->
                        <p><strong>Modification Data:</strong></p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Current Data:</strong>
                                <ul class="list-unstyled">
                                    @foreach ($current as $key => $value)
                                        <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ $value }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <strong>New Data:</strong>
                                <ul class="list-unstyled">
                                    @foreach ($new as $key => $value)
                                        <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ $value }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="consentModal" aria-labelledby="consentModalLabel" data-bs-keyboard="false"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h6 class="modal-title" id="consentModalLabel">Consent & Authorization Agreement</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    {!! $consent->bvn_consent ?? '' !!}
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" id="disagreeBtn" data-bs-dismiss="modal">I
                        Disagree</button>
                    <button type="button" class="btn btn-success" id="agreeBtn">I Agree</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('serviceForm');
            const submitButton = document.getElementById('submitBtn');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const bankSelect = document.getElementById('bank_id');
            const serviceSelect = document.getElementById('service_id');
            const priceDisplay = document.getElementById('priceDisplay');
            const modificationData = document.getElementById('modificationData');
            const form = document.getElementById('serviceForm');
            const submitBtn = document.getElementById('submitBtn');
            const totalPriceEl = document.getElementById('totalPrice');

            let currentBankId = null;
            let currentServicePrice = 0;

            function updateTotalPrice() {
                totalPriceEl.textContent =
                    `₦${currentServicePrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            }

            // Price Update logic simplified without affidavit
            bankSelect.addEventListener('change', function() {
                const bankId = this.value;

                if (!bankId) {
                    serviceSelect.disabled = true;
                    serviceSelect.innerHTML = '<option value="">Select a Service</option>';
                    hideAllSections();
                    return;
                }

                currentBankId = bankId;

                // Fetch services for selected bank
                axios.get(`banks/${bankId}/services`)
                    .then(response => {
                        serviceSelect.disabled = false;
                        serviceSelect.innerHTML = '<option value="">Select a Service</option>';

                        response.data.forEach(service => {
                            const option = document.createElement('option');
                            option.value = service.id;
                            option.textContent =
                                `${service.name} - ₦${service.total_price.toLocaleString()}`;
                            option.dataset.price = service.total_price;
                            serviceSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching services:', error);
                        alert('Error loading services for this bank');
                    });

                hideAllSections();
            });

            serviceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];

                if (!selectedOption.value) {
                    hideAllSections();
                    return;
                }

                currentServicePrice = parseFloat(selectedOption.dataset.price) || 0;

                // Initial price update
                updateTotalPrice();

                priceDisplay.classList.remove('d-none');
                modificationData.classList.remove('d-none');
                submitBtn.disabled = false;
            });

            function hideAllSections() {
                priceDisplay.classList.add('d-none');
                modificationData.classList.add('d-none');
                submitBtn.disabled = true;
                currentServicePrice = 0;
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#consentModal').modal('show');

            $('#disagreeBtn').on('click', function() {
                window.location.href = 'https://ningood.ng/user/dashboard';
            });

            $('#agreeBtn').on('click', function() {
                $('#consentModal').modal('hide');
            });
        });
    </script>
@endpush
