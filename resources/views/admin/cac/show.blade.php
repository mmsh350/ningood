@extends('layouts.dashboard')

@section('title', 'Admin - CAC Company Registration Details')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .form-check .form-check-input {
            margin-left: 0;
        }

        .avatar-rounded {
            border-radius: 50%;
        }

        .section-header {
            background: #f8fafc;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #482666;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title">Company Registration Details</h5>
                            <a href="{{ route('admin.company.index') }}" class="btn btn-light btn-sm fw-bold">
                                <i class="las la-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {!! session('success') !!}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between my-3">
                                <h4 class="mb-0"> </h4>
                                <small class="fw-bold text-muted">(Last Modified - {{ $requests->updated_at }})</small>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="card shadow-sm border-0 mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">Request Information</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- User Details -->
                                                <div class="col-md-6 mb-4">
                                                    <div class="p-3 border rounded bg-light">
                                                        <h6 class="text-uppercase text-muted mb-3">Customer Information</h6>
                                                        <p><strong>Full Name:</strong>
                                                            {{ optional($requests->user)->name ?? 'N/A' }}</p>
                                                        <p><strong>Email:</strong>
                                                            {{ optional($requests->user)->email ?? 'N/A' }}</p>
                                                        <p><strong>Phone:</strong>
                                                            {{ optional($requests->user)->phone_number ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                <!-- Transaction Details -->
                                                <div class="col-md-6 mb-4">
                                                    <div class="p-3 border rounded bg-light">
                                                        <h6 class="text-uppercase text-muted mb-3">Transaction Information
                                                        </h6>
                                                        <p><strong>Transaction ID:</strong>
                                                            {{ optional($requests->transaction)->id ?? 'N/A' }}</p>
                                                        <p><strong>Amount:</strong>
                                                            ₦{{ number_format(optional($requests->transaction)->amount ?? 0, 2) }}
                                                        </p>
                                                        <p><strong>Service Type:</strong>
                                                            {{ optional($requests->transaction)->service_type ?? 'Company Registration' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Registration Details Section -->
                                            <div class="row p-3 border rounded bg-light mx-0">
                                                <!-- Left Column -->
                                                <div class="col-md-6 mb-3 border-end">
                                                    <p><strong>Reference No.:</strong>
                                                        <span class="text-primary fw-bold">{{ $requests->refno }}</span>
                                                    </p>
                                                    <p><strong>Submission Date:</strong>
                                                        {{ $requests->created_at->format('d/m/Y H:i') }}</p>
                                                    <p><strong>Status:</strong>
                                                        @if ($requests->status == 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($requests->status == 'processing')
                                                            <span class="badge bg-primary">Processing</span>
                                                        @elseif($requests->status == 'completed')
                                                            <span class="badge bg-success">Completed</span>
                                                        @elseif($requests->status == 'query')
                                                            <span class="badge bg-info">Queried</span>
                                                        @else
                                                            <span class="badge bg-danger">Failed</span>
                                                        @endif
                                                    </p>
                                                    <hr>
                                                    <h6 class="text-uppercase text-muted mb-2">Director Information</h6>
                                                    <p><strong>Full Name:</strong> {{ $requests->director_surname }}
                                                        {{ $requests->director_firstname }}
                                                        {{ $requests->director_othername }}</p>
                                                    <p><strong>DOB:</strong>
                                                        {{ $requests->director_dob->format('d M, Y') }}</p>
                                                    <p><strong>Gender:</strong> {{ $requests->director_gender }}</p>
                                                    <p><strong>Phone:</strong> {{ $requests->director_phone }}</p>
                                                    <p><strong>Email:</strong> {{ $requests->director_email }}</p>
                                                    <p><strong>NIN:</strong> {{ $requests->director_nin }}</p>

                                                    <h6 class="text-uppercase text-muted mt-3 mb-2">Residential Address</h6>
                                                    <p class="mb-1 small">
                                                        {{ $requests->res_house_number }},
                                                        {{ $requests->res_street_name }}<br>
                                                        {{ $requests->res_city }}, {{ $requests->res_lga }}<br>
                                                        {{ $requests->res_state }} State
                                                    </p>
                                                    <p class="small text-muted"><i>{{ $requests->res_description }}</i></p>
                                                </div>

                                                <!-- Right Column -->
                                                <div class="col-md-6 mb-3">
                                                    <h6 class="text-uppercase text-muted mb-2">Business Information</h6>
                                                    <p><strong>Proposed Name 1:</strong>
                                                        <span
                                                            class="text-dark fw-bold">{{ $requests->business_name_1 }}</span>
                                                    </p>
                                                    <p><strong>Proposed Name 2:</strong>
                                                        {{ $requests->business_name_2 ?? '-' }}</p>
                                                    <p><strong>Nature of Business:</strong>
                                                        {{ $requests->nature_of_business }}</p>
                                                    <p><strong>Business Email:</strong> {{ $requests->business_email }}</p>

                                                    <h6 class="text-uppercase text-muted mt-3 mb-2">Business Address</h6>
                                                    <p class="mb-1 small">
                                                        {{ $requests->bus_house_number }},
                                                        {{ $requests->bus_street_name }}<br>
                                                        {{ $requests->bus_city }}, {{ $requests->bus_lga }}<br>
                                                        {{ $requests->bus_state }} State
                                                    </p>
                                                    <p class="small text-muted"><i>{{ $requests->bus_description }}</i></p>

                                                    <hr>
                                                    <h6 class="text-uppercase text-muted mb-2">Witness Details</h6>
                                                    <p class="mb-0 small"><strong>Name:</strong>
                                                        {{ $requests->witness_surname }}
                                                        {{ $requests->witness_firstname }}
                                                        {{ $requests->witness_othername }}
                                                    </p>
                                                    <p class="mb-0 small"><strong>Phone:</strong>
                                                        {{ $requests->witness_phone }}</p>
                                                    <p class="mb-0 small"><strong>Email:</strong>
                                                        {{ $requests->witness_email }}</p>
                                                    <p class="mb-1 small"><strong>NIN:</strong>
                                                        {{ $requests->witness_nin }}</p>
                                                    <p class="mb-2 small"><strong>Address:</strong>
                                                        <span class="text-muted">{{ $requests->witness_address }}</span>
                                                    </p>

                                                    <h6 class="text-uppercase text-muted mb-2">Shareholder Details</h6>
                                                    <p class="mb-0 small"><strong>Name:</strong>
                                                        {{ $requests->shareholder_surname }}
                                                        {{ $requests->shareholder_firstname }}
                                                        {{ $requests->shareholder_othername }}</p>
                                                    <p class="mb-0 small"><strong>DOB:</strong>
                                                        {{ $requests->shareholder_dob->format('d M, Y') }} |
                                                        <strong>Gender:</strong> {{ $requests->shareholder_gender }}
                                                    </p>
                                                    <p class="mb-0 small"><strong>Nationality:</strong>
                                                        {{ $requests->shareholder_nationality }}</p>
                                                    <p class="mb-0 small"><strong>Email:</strong>
                                                        {{ $requests->shareholder_email }}</p>
                                                    <p class="mb-0 small"><strong>Phone:</strong>
                                                        {{ $requests->shareholder_phone }}</p>
                                                    <p class="mb-1 small"><strong>NIN:</strong>
                                                        {{ $requests->shareholder_nin }}</p>
                                                    <p class="mb-0 small"><strong>Address:</strong>
                                                        <span
                                                            class="text-muted">{{ $requests->shareholder_address }}</span>
                                                    </p>
                                                </div>
                                            </div>

                                            <!-- System Documents -->
                                            <div class="row mt-4">
                                                <div class="col-12 px-0">
                                                    <h6 class="text-uppercase text-muted mb-3 px-3 fw-bold small">Signatures
                                                    </h6>
                                                    <div class="row g-3 mx-0">
                                                        @php
                                                            $docs = [
                                                                'Director Signature' =>
                                                                    $requests->director_signature_path,
                                                                'Witness Signature' =>
                                                                    $requests->witness_signature_path,
                                                                'Shareholder Signature' =>
                                                                    $requests->shareholder_signature_path,
                                                            ];
                                                        @endphp
                                                        @foreach ($docs as $label => $path)
                                                            <div class="col-md-4">
                                                                <div class="card h-100 border-0 shadow-sm overflow-hidden"
                                                                    style="border-radius: 12px; transition: transform 0.2s;">
                                                                    <div class="card-body p-0">
                                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                                            style="height: 140px; border-bottom: 1px solid #eee;">
                                                                            @if ($path && Storage::disk('public')->exists($path))
                                                                                <a href="{{ asset('storage/' . $path) }}"
                                                                                    target="_blank"
                                                                                    class="w-100 h-100 d-flex align-items-center justify-content-center p-3">
                                                                                    <img src="{{ asset('storage/' . $path) }}"
                                                                                        alt="{{ $label }}"
                                                                                        class="img-fluid"
                                                                                        style="max-height: 100%; object-fit: contain;">
                                                                                </a>
                                                                            @else
                                                                                <div class="text-center text-muted">
                                                                                    <i
                                                                                        class="las la-image-slash la-3x opacity-50"></i>
                                                                                    <p class="small mt-2 mb-0">Missing
                                                                                        Signature</p>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <div
                                                                            class="p-3 d-flex justify-content-between align-items-center bg-white">
                                                                            <div>
                                                                                <p class="small fw-bold mb-0 text-dark">
                                                                                    {{ $label }}</p>
                                                                                <span class="text-muted"
                                                                                    style="font-size: 10px;">{{ $path ? 'Signature File' : 'No file uploaded' }}</span>
                                                                            </div>
                                                                            @if ($path && Storage::disk('public')->exists($path))
                                                                                <a href="{{ asset('storage/' . $path) }}"
                                                                                    target="_blank"
                                                                                    class="btn btn-primary btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center"
                                                                                    style="width: 32px; height: 32px;"
                                                                                    title="View Full Image">
                                                                                    <i class="las la-eye"></i>
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            @if (!empty($requests->response_documents))
                                                <div class="row p-3 border rounded bg-light mt-3 mx-0">
                                                    <div class="col-12">
                                                        <h6 class="text-uppercase text-muted mb-2">Admin Response Documents
                                                        </h6>
                                                        <ul class="list-group list-group-flush bg-transparent">
                                                            @foreach ($requests->response_documents as $doc)
                                                                <li
                                                                    class="list-group-item bg-transparent d-flex justify-content-between align-items-center">
                                                                    <span class="small">{{ basename($doc) }}</span>
                                                                    <a href="{{ asset('storage/' . $doc) }}"
                                                                        target="_blank"
                                                                        class="btn   btn-primary text-light">
                                                                        Download
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Action Section -->
                                    <div class="p-4 border rounded bg-light mt-4 shadow-sm">
                                        <h6 class="text-uppercase text-muted mb-4 fw-bold">Update Request Status</h6>
                                        <form action="{{ route('admin.company.update-status', $requests->id) }}"
                                            method="POST" id="statusForm" enctype="multipart/form-data">
                                            @csrf

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Select Status</label>
                                                    <select name="status" id="status" class="form-select text-dark"
                                                        required>
                                                        <option value="" disabled selected>-- Choose Status --
                                                        </option>
                                                        <option value="completed">Completed</option>
                                                        <option value="processing">Processing</option>
                                                        <option value="query">Query / Resubmit</option>
                                                        <option value="failed">Failed / Rejected</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Upload Response Documents
                                                        (Optional)</label>
                                                    <input type="file" name="document[]" multiple
                                                        class="form-control">
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="delete_old_docs" id="delete_old_docs">
                                                        <label class="form-check-label small fw-bold"
                                                            for="delete_old_docs">
                                                            Delete old response documents
                                                        </label>
                                                    </div>
                                                    <small class="text-muted d-block mt-1">You can select multiple files
                                                        (PDF/Images)</small>
                                                </div>
                                            </div>

                                            <!-- Refund Option -->
                                            <div class="mb-4 d-none p-3 border rounded bg-white shadow-sm"
                                                id="refundSection">
                                                <label class="form-label fw-bold text-danger">Refund Options</label>
                                                <div class="d-flex gap-3 mb-3">
                                                    @foreach ([10, 20, 30, 50, 100] as $pct)
                                                        <div class="form-check">
                                                            <input type="radio" name="refund_percentage"
                                                                value="{{ $pct }}"
                                                                id="refund{{ $pct }}"
                                                                class="form-check-input refund-percentage">
                                                            <label for="refund{{ $pct }}"
                                                                class="form-check-label">{{ $pct }}%</label>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="col-md-4">
                                                    <label for="refundAmount" class="form-label small fw-bold">Refund
                                                        Amount (₦)</label>
                                                    <input type="number" id="refundAmount" name="refundAmount"
                                                        class="form-control" value="0">
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Administrator Comment / Feedback</label>
                                                <div id="editor"
                                                    style="height: 200px; background: #fff; border-radius: 5px;">
                                                    {!! $requests->admin_comment !!}
                                                </div>
                                                <input type="hidden" name="comment" id="commentInput">
                                            </div>

                                            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                                <i class="las la-save me-1"></i> Update Registration Status
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Quill Editor
            const quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: 'Enter feedback for the user...',
            });

            const statusSelect = document.getElementById('status');
            const refundSection = document.getElementById('refundSection');
            const refundAmountInput = document.getElementById('refundAmount');
            const refundPercentageRadios = document.querySelectorAll('.refund-percentage');
            const transactionAmount = {{ optional($requests->transaction)->amount ?? 0 }};

            // Toggle Refund Section
            statusSelect.addEventListener('change', function() {
                if (this.value === 'failed') {
                    refundSection.classList.remove('d-none');
                } else {
                    refundSection.classList.add('d-none');
                    refundAmountInput.value = '0';
                    refundPercentageRadios.forEach(radio => radio.checked = false);
                }

                // Auto-fill common processing message
                if (this.value === 'processing' && quill.getText().trim().length === 0) {
                    quill.root.innerHTML =
                        "Your company registration request has been received and is currently being processed by the CAC. We will update you once there is further progress.";
                }
            });

            // Calculate refund amount based on percentage
            refundPercentageRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const percentage = parseInt(this.value, 10);
                    const amount = (transactionAmount * percentage) / 100;
                    refundAmountInput.value = Math.round(amount);
                });
            });

            // Form Submit Logic
            const form = document.getElementById('statusForm');
            form.addEventListener('submit', function(e) {
                document.getElementById('commentInput').value = quill.root.innerHTML;

                if (quill.getText().trim().length === 0) {
                    e.preventDefault();
                    alert('Please provide a comment or feedback for the user.');
                }
            });
        });
    </script>
@endpush
