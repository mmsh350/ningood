@extends('layouts.dashboard')

@section('title', 'BVN User Request')
@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .form-check .form-check-input {
            margin-left: 0;
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
                    <div class="card custom-card ">
                        <div class="card-header">
                            <h5 class="card-title">BVN User Request</h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {!! session('success') !!}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Page Header -->
                            <div class="d-flex align-items-center justify-content-between my-3">
                                <h4 class="mb-0">Request Details </h4>
                                <small class="pull-right fw-bold"> (Last Modified - {{ $requests->updated_at }})</small>
                            </div>
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {!! session('success') !!}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif


                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <!-- Request Details Card -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">Details</h5>

                                        </div>
                                        <div class="card-body">
                                            <!-- Grid Layout for User and Transaction Details -->
                                            <div class="row">
                                                <!-- User Details -->
                                                <div class="col-md-6 mb-4">
                                                    <div class="p-3 border rounded bg-light">
                                                        <h6 class="text-uppercase text-muted mb-3">Customer Information</h6>
                                                        <p> &nbsp;<strong>Full
                                                                Name:</strong>
                                                            {{ optional($requests->user)->name ?? 'N/A' }}</p>
                                                        <p>  &nbsp;<strong>Email:</strong>
                                                            {{ optional($requests->user)->email ?? 'N/A' }}</p>
                                                        <p>&nbsp;<strong>Phone:</strong>
                                                            {{ optional($requests->user)->phone_number ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                <!-- Transaction Details -->
                                                <div class="col-md-6 mb-4">
    <div class="p-3 border rounded bg-light">
        <h6 class="text-uppercase text-muted mb-3">Transaction Information</h6>

        <p><strong>Transaction ID:</strong>
            {{ optional($requests->transactions)->id ?? 'N/A' }}
        </p>

        <p><strong>Amount:</strong>
            ₦{{ number_format(optional($requests->transactions)->amount ?? 0, 2) }}
        </p>

        <p><strong>Service Type:</strong>
            {{ optional($requests->transactions)->service_type }}

        </p>
    </div>
</div>

                                            </div>

                                            <!-- Request Details Section -->

 <div class="row p-3 border rounded bg-light">
                                    <div class="col-md-6">

                                        <p><strong>Reference No.:</strong> {{ strtoupper($requests->refno) }}</p>
                                        <p><strong>Request Type.:</strong> {{ strtoupper(optional($requests->transactions)->service_type ?? 'N/A') }}</p>

                                        <p><strong>BVN NO.:</strong> {{ $requests->bvn }}</p>
                                        <p><strong>Bank Name.:</strong> {{ $requests->bank_name }}</p>
                                        <p><strong>Account Number:</strong> {{ $requests->account_number }}</p>
                                        <p><strong>Account Name.:</strong> {{ $requests->account_name }}</p>
                                        <p><strong>Date:</strong>
                                            {{ \Carbon\Carbon::parse($requests->created_at)->format('d/m/Y') }}</p>
                                        <p><strong>Status:</strong>
                                            @if ($requests->status == 'submitted')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($requests->status == 'successful')
                                                <span class="badge bg-success">Successful</span>
                                            @elseif($requests->status == 'processing')
                                                <span class="badge bg-primary">Processing</span>
                                            @else
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </p>

                                    </div>
                                    <div class="col-md-6">

                                        <p><strong>Full Name.:</strong> {{ $requests->fullname }}</p>
                                        <p><strong>Username.:</strong> {{ $requests->username }}</p>
                                        <p><strong>Email ID.:</strong> {{ $requests->email }}</p>
                                        <p><strong>Phone No.:</strong> {{ $requests->phone_number }}</p>
                                        <p><strong>State.:</strong> {{ $requests->state }}</p>
                                        <p><strong>LGA.:</strong> {{ $requests->lga }}</p>
                                        <p><strong>Address.:</strong> {{ $requests->address }}</p>

                                    </div>

                                     <p class="mt-2"><strong>Comments:</strong><br /> {!! $requests->reason !!}</p>
                                </div>

                            </div>
                        </div>



                                            <!-- Comment and Action Section -->
                                            <div class="p-3 border rounded bg-light mt-5">
                                                <h6 class="text-uppercase text-muted mb-3">Action</h6>
                                                <form
                                                    action="{{ route('admin.update-request-status2', [$requests->id, $request_type]) }}"
                                                    method="POST" id="statusForm">
                                                    @csrf

                                                    <!-- Status Selection -->
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label"><strong>Select
                                                                Status</strong></label>
                                                        <select name="status" id="status" class="form-select text-dark"
                                                            required>
                                                            <option value="" disabled selected>-- Choose Status --
                                                            </option>
                                                            <option value="successful">Resolved</option>
                                                            <option value="processing">Processing</option>
                                                            <option value="rejected">Rejected</option>
                                                        </select>
                                                    </div>

                                                    <!-- Refund Option -->
                                                    <div class="mb-3 d-none" id="refundOption">
                                                        <label class="form-label"><strong>Refund Options</strong></label>

                                                        <!-- Percentage Selection -->
                                                        <div class="d-flex gap-3">
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="10" id="refund10"
                                                                    class="form-check-input refund-percentage ">
                                                                <label for="refund10" class="form-check-label">10%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="20" id="refund20"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund20" class="form-check-label">20%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="30" id="refund30"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund30" class="form-check-label">30%</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="radio" name="refund_percentage"
                                                                    value="50" id="refund50"
                                                                    class="form-check-input refund-percentage">
                                                                <label for="refund50" class="form-check-label">50%</label>
                                                            </div>
                                                        </div>

                                                        <!-- Calculated Refund Amount -->
                                                        <div class="mt-3">
                                                            <label for="refundAmount" class="form-label"><strong>Refund
                                                                    Amount
                                                                    (₦)</strong></label>
                                                            <input type="text" id="refundAmount" name="refundAmount"
                                                                class="form-control">
                                                        </div>
                                                    </div>


                                                    <!-- Quill Editor Section -->
                                                    <div class="mb-3">
                                                        <label for="editor"
                                                            class="form-label"><strong>Comment</strong></label>
                                                        <div id="editor" class="form-control"> </div>
                                                        <input type="hidden" name="comment" id="commentInput">
                                                    </div>

                                                    <!-- Submit Button -->
                                                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                                                </form>

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
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Initialize Quill Editor
                        const quill = new Quill('#editor', {
                            theme: 'snow',
                            placeholder: 'Enter your comment...',
                        });

                        function clear() {
                            quill.root.innerHTML = '';
                        }
                        // Toggle Refund Option
                        const statusSelect = document.getElementById('status');
                        const refundOption = document.getElementById('refundOption');
                        statusSelect.addEventListener('change', function() {
                            clear();
                            if (this.value === 'rejected') {

                                refundOption.classList.remove('d-none');
                            } else if (this.value === 'processing') {
                                quill.root.innerHTML =
                                    "Thank you for reaching out. Your request has been received and is currently being processed. We will notify you promptly upon resolution."
                            } else {
                                refundOption.classList.add('d-none');
                            }
                        });

                        // Handle Form Submission
                        const form = document.getElementById('statusForm');
                        form.addEventListener('submit', function(event) {
                            // Get Quill content as HTML
                            const commentContent = quill.root.innerHTML;
                            // Set it in the hidden input
                            document.getElementById('commentInput').value = commentContent;

                            // Optionally: Validate the comment is not empty
                            if (quill.getText().trim().length === 0) {
                                event.preventDefault();
                                alert('Please add a comment before submitting.');
                            }
                        });
                    });
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const statusSelect = document.getElementById('status');
                        const refundOption = document.getElementById('refundOption');
                        const refundAmountInput = document.getElementById('refundAmount');
                        const refundPercentageRadios = document.querySelectorAll('.refund-percentage');

                        // Transaction amount (Replace with actual value if dynamic)
                        const transactionAmount = {{ optional($requests->transactions)->amount ?? 0 }};

                        // Show or hide refund option based on status
                        statusSelect.addEventListener('change', function() {
                            if (this.value === 'rejected') {
                                refundOption.classList.remove('d-none');
                                refundAmountInput.setAttribute('required', 'required');

                            } else {
                                refundOption.classList.add('d-none');
                                refundAmountInput.removeAttribute('required');
                                refundAmountInput.value = '';
                                refundPercentageRadios.forEach(radio => (radio.checked = false));
                            }
                        });

                        // Calculate refund amount based on selected percentage
                        refundPercentageRadios.forEach(radio => {
                            radio.addEventListener('change', function() {
                                const percentage = parseInt(this.value, 10);
                                const refundAmount = (transactionAmount * percentage) / 100;
                                refundAmountInput.value = `${refundAmount}`;
                            });
                        });
                    });
                </script>

                <!-- Quill Editor JS -->
                <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
                <!-- Internal Quill JS -->
            @endpush
