@extends('layouts.dashboard')

@section('title', 'BVN Modification Request')
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

        /* Fix alignment for stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .form-check .form-check-input {
            margin-left: 0;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
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

                    <!-- Search and Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-6 mb-2">
                            <form method="GET" action="{{ route('admin.bvn-modification.index') }}" class="form-inline">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search by Ref No, BVN or NIN" value="{{ request('search') }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Search</button>
                                        <a href="{{ route('admin.bvn-modification.index') }}"
                                            class="btn btn-secondary ml-1">Reset</a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-3 mb-2">
                            <form method="GET" action="{{ route('admin.bvn-modification.index') }}">
                                <select name="status" onchange="this.form.submit()" class="form-select text-dark">
                                    <option value="">Filter by Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>
                                        Processing</option>
                                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>
                                        Resolved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                    <option value="query" {{ request('status') == 'query' ? 'selected' : '' }}>
                                        Query</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Ref No</th>
                                    <th>Bank</th>
                                    <th>Service</th>
                                    <th>Amount (₦)</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($modificationRequests as $index => $req)
                                    @php
                                        $data = $req->modification_data;
                                        $current = $data['current_data'] ?? [];
                                        $new = $data['new_data'] ?? [];
                                    @endphp
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
                                            <button type="button" class="btn btn-sm btn-outline-dark"
                                                data-bs-toggle="modal" data-bs-target="#detailsModal{{ $req->id }}">
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No modification requests found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <div class="row mb-3 border-bottom">
                            <div class="col-md-6 border-end">
                                <p class="mb-0"><strong>Customer Name:</strong></p>
                                <p class="text-primary fw-bold mb-2">{{ strtoupper(optional($req->user)->name ?? 'N/A') }}
                                </p>

                                <p class="mb-0"><strong>Email:</strong></p>
                                <p class="text-primary fw-bold mb-2">{{ optional($req->user)->email ?? 'N/A' }}
                                </p>

                                <p class="mb-0"><strong>Phone No.:</strong></p>
                                <p class="text-primary fw-bold mb-0">
                                    {{ optional($req->user)->phone_number ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-0"><strong>Transaction Reference:</strong></p>
                                <p class="text-primary fw-bold mb-2">
                                    {{ strtoupper($req->transactions?->referenceId ?? 'N/A') }}</p>

                                <p class="mb-0"><strong>Amount Paid:</strong></p>
                                <p class="text-primary fw-bold mb-0">
                                    ₦{{ number_format($req->transactions?->amount, 2) ?? '0.00' }}</p>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>BVN Number:</strong></p>
                                <p class="text-primary fw-bold">{{ strtoupper($req->bvn_no) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>NIN Number:</strong></p>
                                <p class="text-primary fw-bold">{{ strtoupper($req->nin_number) }}</p>
                            </div>
                        </div>

                        <!-- Grid Layout for User and Transaction Details -->
                        <div class="row">
                            <!-- User Details -->
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light">
                                    <h6 class="text-uppercase text-muted mb-3">Customer Information</h6>
                                    <p><i class="ti ti-user fs-16"></i> &nbsp;<strong>Full Name:</strong>
                                        {{ strtoupper(optional($req->user)->name ?? 'N/A') }}</p>
                                    <p><i class="ti ti-mail fs-16"></i> &nbsp;<strong>Email:</strong>
                                        {{ strtoupper(optional($req->user)->email ?? 'N/A') }}</p>
                                    <p><i class="ti ti-phone fs-16"></i> &nbsp;<strong>Phone:</strong>
                                        {{ strtoupper(optional($req->user)->phone_number ?? 'N/A') }}</p>
                                </div>
                            </div>
                            <!-- Transaction Details -->
                            <div class="col-md-6 mb-4">
                                <div class="p-3 border rounded bg-light">
                                    <h6 class="text-uppercase text-muted mb-3">Transaction Information</h6>
                                    <p><strong>Reference No:</strong> {{ $req->refno }}</p>
                                    <p><strong>Amount:</strong> ₦{{ number_format($req->total_price, 2) }}</p>
                                    <p><strong>Bank:</strong> {{ optional($req->bank)->name ?? 'N/A' }}</p>
                                    <p><strong>Service:</strong> {{ optional($req->service)->name ?? 'N/A' }}
                                    </p>
                                    <p><strong>Status:</strong>
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
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Previous Comments -->
                        @if ($req->reason)
                            <div class="mb-4">
                                <div class="p-3 border rounded bg-light text-start">
                                    <h6 class="text-uppercase text-muted mb-3">Previous Comment</h6>
                                    <div class="small text-dark">{!! $req->reason !!}</div>
                                </div>
                            </div>
                        @endif

                        <hr>

                        <!-- Modification Data -->
                        <p><strong>Modification Data:</strong></p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Current Data:</strong>
                                <ul class="list-unstyled">
                                    @foreach ($current as $key => $value)
                                        <li><strong>{{ strtoupper(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ strtoupper($value) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <strong>New Data:</strong>
                                <ul class="list-unstyled">
                                    @foreach ($new as $key => $value)
                                        <li><strong>{{ strtoupper(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ strtoupper($value) }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <!-- Admin Actions -->
                        <div class="p-3 border rounded mt-2 bg-light text-start">
                            <h6 class="text-uppercase text-muted mb-3">Action</h6>
                            <form action="{{ route('admin.bvn-modification.update-status', $req->id) }}" method="POST"
                                id="statusForm_{{ $req->id }}">
                                @csrf
                                <!-- Status Selection -->
                                <div class="mb-3">
                                    <label for="status" class="form-label"><strong>Select Status</strong></label>
                                    <select name="status" id="status_{{ $req->id }}"
                                        class="form-select status-select text-dark" required>
                                        <option value="pending" {{ $req->status == 'pending' ? 'selected' : '' }}>Pending
                                        </option>
                                        <option value="processing" {{ $req->status == 'processing' ? 'selected' : '' }}>
                                            Processing</option>
                                        <option value="resolved" {{ $req->status == 'resolved' ? 'selected' : '' }}>
                                            Resolved</option>
                                        <option value="rejected" {{ $req->status == 'rejected' ? 'selected' : '' }}>
                                            Rejected</option>
                                        <option value="query" {{ $req->status == 'query' ? 'selected' : '' }}>Query/Edit
                                        </option>
                                    </select>
                                </div>

                                <!-- Refund Option (Matches view-mod-request pattern) -->
                                <div class="mb-3 d-none" id="refundOption_{{ $req->id }}">
                                    <label class="form-label"><strong>Refund Options</strong></label>

                                    <!-- Percentage Selection -->
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input type="radio" name="refund_percentage_{{ $req->id }}"
                                                value="10" id="refund10_{{ $req->id }}"
                                                class="form-check-input refund-percentage">
                                            <label for="refund10_{{ $req->id }}"
                                                class="form-check-label">10%</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" name="refund_percentage_{{ $req->id }}"
                                                value="20" id="refund20_{{ $req->id }}"
                                                class="form-check-input refund-percentage">
                                            <label for="refund20_{{ $req->id }}"
                                                class="form-check-label">20%</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" name="refund_percentage_{{ $req->id }}"
                                                value="30" id="refund30_{{ $req->id }}"
                                                class="form-check-input refund-percentage">
                                            <label for="refund30_{{ $req->id }}"
                                                class="form-check-label">30%</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" name="refund_percentage_{{ $req->id }}"
                                                value="50" id="refund50_{{ $req->id }}"
                                                class="form-check-input refund-percentage">
                                            <label for="refund50_{{ $req->id }}"
                                                class="form-check-label">50%</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" name="refund_percentage_{{ $req->id }}"
                                                value="100" id="refund100_{{ $req->id }}"
                                                class="form-check-input refund-percentage">
                                            <label for="refund100_{{ $req->id }}"
                                                class="form-check-label">100%</label>
                                        </div>
                                    </div>

                                    <!-- Calculated Refund Amount -->
                                    <div class="mt-3">
                                        <label for="refund_amount_{{ $req->id }}" class="form-label"><strong>Refund
                                                Amount (₦)</strong></label>
                                        <input type="number" id="refund_amount_input_{{ $req->id }}"
                                            name="refund_amount" class="form-control" step="0.01"
                                            value="{{ $req->total_price }}">
                                        <small class="text-muted">Original Amount:
                                            ₦{{ number_format($req->total_price, 2) }}</small>
                                    </div>
                                </div>

                                <!-- Quill Editor Section -->
                                <div class="mb-3">
                                    <label class="form-label"><strong>Comment</strong></label>
                                    <div id="editor_{{ $req->id }}" class="form-control" style="height: 150px;">
                                    </div>
                                    <input type="hidden" name="reason" id="commentInput_{{ $req->id }}">
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@push('scripts')
    <!-- Quill Editor JS -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalPriceMap = @json($modificationRequests->getCollection()->pluck('total_price', 'id'));
            const quillInstances = {};

            // Initialize Quill for each modal
            document.querySelectorAll('[id^="editor_"]').forEach(editorDiv => {
                const id = editorDiv.id.split('_')[1];
                const quill = new Quill(`#${editorDiv.id}`, {
                    theme: 'snow',
                    placeholder: 'Enter your comment here...',
                });
                quillInstances[id] = quill;

                // Handle Form Submission for this modal
                const form = document.getElementById(`statusForm_${id}`);
                const commentInput = document.getElementById(`commentInput_${id}`);

                form.addEventListener('submit', function(e) {
                    commentInput.value = quill.root.innerHTML;
                    if (quill.getText().trim().length === 0) {
                        e.preventDefault();
                        alert('Please enter a comment before submitting.');
                    }
                });
            });

            // Handle Status Change Toggling
            document.querySelectorAll('.status-select').forEach(select => {
                const id = select.id.split('_')[1];
                const refundOption = document.getElementById(`refundOption_${id}`);
                const refundAmountInput = document.getElementById(`refund_amount_input_${id}`);
                const refundPercentageRadios = document.querySelectorAll(
                    `input[name="refund_percentage_${id}"]`);
                const quill = quillInstances[id];

                select.addEventListener('change', function() {
                    const status = this.value;

                    // Show/Hide Refund
                    if (status === 'rejected') {
                        refundOption.classList.remove('d-none');
                        refundAmountInput.setAttribute('required', 'required');
                        if (quill) quill.root.innerHTML = "";
                    } else if (status === 'processing') {
                        refundOption.classList.add('d-none');
                        refundAmountInput.removeAttribute('required');
                        if (quill) quill.root.innerHTML =
                            "Thank you for reaching out. Your request has been received and is currently being processed. We will notify you promptly upon resolution.";
                    } else {
                        refundOption.classList.add('d-none');
                        refundAmountInput.removeAttribute('required');
                        refundAmountInput.value = totalPriceMap[id];
                        refundPercentageRadios.forEach(radio => (radio.checked = false));
                        if (quill) quill.root.innerHTML = "";
                    }
                });

                // Initial check
                if (select.value === 'rejected') {
                    refundOption.classList.remove('d-none');
                    refundAmountInput.setAttribute('required', 'required');
                }
            });

            // Handle Percentage to Amount Calculation
            document.querySelectorAll('.refund-percentage').forEach(radio => {
                radio.addEventListener('change', function() {
                    const id = this.name.split('_')[2];
                    const percentage = parseInt(this.value, 10);
                    const transactionAmount = totalPriceMap[id];
                    const refundAmountInput = document.getElementById(`refund_amount_input_${id}`);

                    if (transactionAmount) {
                        const calculatedAmount = (transactionAmount * percentage) / 100;
                        refundAmountInput.value = calculatedAmount.toFixed(2);
                    }
                });
            });

            // Allow manual amount adjustment
            document.querySelectorAll('[id^="refund_amount_input_"]').forEach(input => {
                input.addEventListener('input', function() {
                    const id = this.id.split('_')[3];
                    const refundPercentageRadios = document.querySelectorAll(
                        `input[name="refund_percentage_${id}"]`);
                    refundPercentageRadios.forEach(radio => (radio.checked = false));
                });
            });
        });
    </script>
@endpush
