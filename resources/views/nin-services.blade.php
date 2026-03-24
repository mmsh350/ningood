@extends('layouts.dashboard')

@section('title', 'NIN Services')
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

        .mobile-card {
            border-radius: 12px;
            border: 1px solid #edf2f7;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .mobile-card:active {
            transform: scale(0.98);
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class=" grid-margin stretch-card col-md-12   grid-margin stretch-card ">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12 mb-3">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
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
                            <div class="mb-2">
                                <h6 class="text-center text-uppercase text-muted fw-semibold mb-3"
                                    style="font-size: 0.85rem;">
                                    Total NIN Validation Requests
                                </h6>

                                <div class="row g-2 justify-content-center">
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
                                                'value' => $totalInProgress,
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
                                        ];
                                    @endphp

                                    @foreach ($validationStats as $stat)
                                        <div class="col-6 col-sm-3 col-lg-2">
                                            <div class="border rounded-3 text-center py-2 px-1 shadow-sm {{ $stat['text'] }}"
                                                style="background: {{ $stat['bg'] }}; font-size: 0.85rem;">
                                                <div class="small fw-light mb-1">{{ $stat['label'] }}</div>
                                                <div class="fw-bold" style="font-size: 1.1rem;">{{ $stat['value'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title mb-1">NIN Validation</h4>
                        <p class="card-description mb-3">
                            Choose how you want to submit your NIN request.
                        </p>

                        <ul class="nav nav-pills justify-content-center mb-4" id="ninTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="single-tab" data-bs-toggle="pill"
                                    data-bs-target="#singleNin" type="button" role="tab">
                                    <i class="las la-user"></i> Single Request
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bulk-tab" data-bs-toggle="pill" data-bs-target="#bulkNin"
                                    type="button" role="tab">
                                    <i class="las la-layer-group"></i> Bulk Upload
                                </button>
                            </li>
                        </ul>


                        <div class="row">
                            <div class="col-md-12 mb-3" role="tabpanel" aria-labelledby="new-tab">

                                <div class="text-center">
                                    <img src="{{ asset('assets/images/img/nimc.png') }}" class="img-fluid mx-auto d-block"
                                        style="max-width: 180px; width: 100%;" alt="NIMC Logo">
                                </div>

                                <center>
                                    <small class="font-italic text-danger"><i>Please note that this request will be
                                            processed within 2 working days. We appreciate your patience
                                            and
                                            will keep you updated on the status.
                                        </i>
                                    </small>
                                </center>

                                <div class="tab-content" id="ninTabContent">

                                    <div class="tab-pane fade" id="bulkNin" role="tabpanel" aria-labelledby="bulk-tab">


                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0 fw-semibold text-uppercase">
                                                    Bulk NIN Validation
                                                </h6>
                                            </div>

                                            <div class="card-body">

                                                <div class="  small">
                                                    <strong>Note:</strong>
                                                    Each NIN is charged individually.
                                                    Processing time is within <strong>2 working days</strong>.
                                                </div>

                                                <form method="POST" action="{{ route('user.nin.services.bulk') }}"
                                                    id="bulkNinForm">

                                                    @csrf
                                                    <input type="hidden" name="service" value="113">

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">
                                                            Enter NINs (one per line)
                                                        </label>

                                                        <textarea name="nins" id="bulkNins" class="form-control" rows="6" placeholder="12345678901&#10;10987654321"
                                                            required></textarea>

                                                        <div class="form-text">
                                                            Only <strong>11-digit numeric</strong> NINs are allowed.
                                                        </div>
                                                    </div>

                                                    {{-- LIVE STATS --}}
                                                    <div class="row text-center mb-2">

                                                        <div class="col-4">
                                                            <div class="border rounded py-1 px-2 bg-light">
                                                                <small class="text-muted d-block">Total</small>
                                                                <div class="fw-bold small" id="totalCount">0</div>
                                                            </div>
                                                        </div>

                                                        <div class="col-4">
                                                            <div class="border rounded py-1 px-2 bg-light">
                                                                <small class="text-muted d-block">Valid</small>
                                                                <div class="fw-bold small text-success" id="validCount">0
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-4">
                                                            <div class="border rounded py-1 px-2 bg-light">
                                                                <small class="text-muted d-block">Invalid</small>
                                                                <div class="fw-bold small text-danger" id="invalidCount">0
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>


                                                    <div class="d-flex justify-content-end">
                                                        <button type="submit" class="btn text-light px-4"
                                                            style="background:#2563eb" id="bulkSubmitBtn">
                                                            <i class="las la-upload"></i> Submit Bulk Request
                                                        </button>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade show active" id="singleNin" role="tabpanel"
                                        aria-labelledby="single-tab">

                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">

                                                <div class="alert alert-info small">
                                                    Use this option to submit a <strong>single NIN request</strong>.
                                                </div>

                                                <form id="form" name="nin-request" method="POST"
                                                    action="{{ route('user.nin.services.request') }}"
                                                    enctype="multipart/form-data">
                                                    @csrf

                                                    <div class="row">
                                                        <div class="col-md-12 mt-3 mb-3">
                                                            <select name="service" id="service"
                                                                class="form-select text-dark" required>
                                                                <option value="">-- Service Type --</option>
                                                                @foreach ($services as $service)
                                                                    <option value="{{ $service->service_code }}">
                                                                        {{ $service->name }} -
                                                                        &#x20A6;{{ number_format($service->price, 2) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="col-md-12">
                                                            <p class="mb-2 form-label" id="modify_lbl"></p>
                                                            <div id="input-container"></div>
                                                        </div>


                                                        <button type="submit" id="nin-request" class="btn text-light"
                                                            style="background:#2563eb">
                                                            <i class="las la-share"></i> Submit Request
                                                        </button>
                                                    </div>

                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>


                            <div class="col-md-12" role="tabpanel" aria-labelledby="history-tab">

                                <form method="GET" action="{{ route('user.nin.services') }}"
                                    class="row g-2 mb-3 mt-2 align-items-end">

                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label for="search" class="form-label d-block d-md-none">Search</label>
                                            <input type="text" id="search" name="search" class="form-control"
                                                value="{{ request('search') }}" placeholder="Search Here ...">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="date_from" class="form-label d-block d-md-none">Start
                                                Date</label>
                                            <input type="date" id="date_from" name="date_from" class="form-control"
                                                value="{{ request('date_from') }}" placeholder="Start Date">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="date_to" class="form-label d-block d-md-none">End
                                                Date</label>
                                            <input type="date" id="date_to" name="date_to" class="form-control"
                                                value="{{ request('date_to') }}" placeholder="End Date">
                                        </div>

                                        <div class="col-md-3">
                                            <span class="form-label d-block d-md-none">&nbsp;</span>
                                            <button type="submit" class="btn text-light w-100"
                                                style="background:#2563eb">Filter</button>
                                        </div>
                                    </div>
                                </form>
                                @if (!$ninServices->isEmpty())

                                    @php
                                        $currentPage = $ninServices->currentPage();
                                        $perPage = $ninServices->perPage();
                                        $serialNumber = ($currentPage - 1) * $perPage + 1;
                                    @endphp

                                    <!-- DESKTOP TABLE VIEW -->
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table text-nowrap" style="background:#fafafc !important">
                                            <thead>
                                                <tr class="table-primary">
                                                    <th width="5%">ID</th>
                                                    <th>NIN Number</th>
                                                    <th class="text-center">Status</th>
                                                    <th>Date</th>
                                                    <th>Service Type</th>
                                                    <th>Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ninServices as $data)
                                                    <tr>
                                                        <th>{{ $serialNumber++ }}</th>
                                                        <td>{{ $data->nin_number }}</td>
                                                        <td class="text-center">
                                                            @php
                                                                $badgeClass = match ($data->status) {
                                                                    'Successful' => 'badge bg-success',
                                                                    'Failed' => 'badge bg-danger',
                                                                    'In-Progress' => 'badge bg-primary',
                                                                    default => 'badge bg-warning',
                                                                };
                                                            @endphp
                                                            <span
                                                                class="{{ $badgeClass }}">{{ Str::upper($data->status) }}</span>
                                                        </td>
                                                        <td>{{ $data->created_at }}</td>
                                                        <td>{{ $data->description }}</td>
                                                        <td>{!! $data->reason !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- MOBILE CARD VIEW -->
                                    <div class="d-md-none">
                                        @foreach ($ninServices as $data)
                                            @php
                                                $badgeClass = match ($data->status) {
                                                    'Successful' => 'badge bg-success',
                                                    'Failed' => 'badge bg-danger',
                                                    'In-Progress' => 'badge bg-primary',
                                                    default => 'badge bg-warning',
                                                };
                                            @endphp
                                            <div class="card mobile-card shadow-sm border-0">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <div class="small text-muted mb-1">
                                                                <i class="las la-id-card"></i> NIN:
                                                                {{ $data->nin_number }}
                                                            </div>
                                                            <h6 class="mb-0 fw-bold text-dark">{{ $data->description }}
                                                            </h6>
                                                        </div>
                                                        <span
                                                            class="{{ $badgeClass }} small">{{ Str::upper($data->status) }}</span>
                                                    </div>

                                                    @if ($data->reason)
                                                        <div
                                                            class="bg-light rounded p-2 my-2 border-start border-secondary border-3 small">
                                                            <strong>Response:</strong> {!! $data->reason !!}
                                                        </div>
                                                    @endif

                                                    <div
                                                        class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                                        <small class="text-muted small">
                                                            <i class="las la-calendar"></i> {{ $data->created_at }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Pagination Links -->
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $ninServices->links('vendor.pagination.bootstrap-4') }}
                                    </div>
                                @else
                                    <center>
                                        <img width="65%" src="{{ asset('assets/images/no-transaction.gif') }}"
                                            alt="">
                                    </center>
                                    <p class="text-center fw-semibold fs-15">No Request Available!</p>
                                @endif

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="reason" tabindex="-1" aria-labelledby="reason" data-bs-keyboard="true"
                aria-hidden="true">
                <!-- Scrollable modal -->
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="staticBackdropLabel2">Support
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p id="message">No Message Yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $("#reason").on("shown.bs.modal", function(event) {
            var button = $(event.relatedTarget);

            var reason = button.data("reason");
            if (reason != "") $("#message").html(reason);
            else $("#message").html("No Message Yet.");
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form');
            const submitButton = document.getElementById('nin-request');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });

        $(document).ready(function() {
            hide();

            $("#service").change(function() {
                const selectedItem = this.value;

                // Clear dynamic content area
                $("#input-container").empty();
                $("#modify_lbl").text("").hide();

                let labelText = "";
                let inputs = '';

                switch (selectedItem) {
                    case '000':
                        // Requirements: NIN, Email
                        labelText = "Enter NIN and Email Address";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createInput('email', 'Email Address', 100, 'email');
                        break;

                    case '113':
                    case '114':
                        // Requirement: Only NIN
                        labelText = "Enter NIN Number";
                        inputs += createInput('nin', 'Enter NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        break;

                    case '001':
                        // Requirements: NIN, Tracking ID, Surname, First Name, Middle Name, DOB
                        labelText = "Full Identity Details";
                        inputs += createInput('nin', 'Enter NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createInput('tracking_id', 'Tracking ID', '15', 'text',
                            '^(?=.*[a-zA-Z])(?=.*\\d)[a-zA-Z0-9]{15}$',
                            'Tracking ID must be 15 characters, containing letters and numbers');
                        inputs += createInput('surname', 'Surname');
                        inputs += createInput('firstname', 'First Name');
                        inputs += createInput('middlename', 'Middle Name', '', 'text', '',
                            '', '');
                        inputs += createInput('dob', 'Date of Birth', '', 'date', '',
                            'Date of Birth is required!');
                        break;

                    default:
                        break;
                }

                $("#modify_lbl").text(labelText).show();
                $("#input-container").append(inputs);

                if (selectedItem === '115') {
                    const dobInput = document.getElementById('dob');

                    if (dobInput) {
                        dobInput.addEventListener('invalid', function() {
                            if (dobInput.validity.valueMissing) {
                                dobInput.setCustomValidity('Date of Birth is required!');
                            } else {
                                dobInput.setCustomValidity('');
                            }
                        });

                        dobInput.addEventListener('input', function() {
                            dobInput.setCustomValidity('');
                        });

                        // Optional: Prevent future dates
                        dobInput.max = new Date().toISOString().split("T")[0];
                    }
                }
            });
        });

        function hide() {
            $("#modify_lbl").hide();
        }

        function createInput(id, placeholder, maxlength = '', type = 'text', pattern = '', title = '', required =
            'required') {
            const max = maxlength ? `maxlength="${maxlength}"` : '';
            const pat = pattern ? `pattern="${pattern}"` : '';
            const tip = title ? `title="${title}"` : '';


            return `<input type="${type}" name="${id}" id="${id}" ${max} ${pat} ${tip} class="form-control mb-2" placeholder="${placeholder}" ${required} />`;
        }
    </script>
@endpush

@push('scripts')
    {{-- <script>
        $("#reason").on("shown.bs.modal", function(event) {
            var button = $(event.relatedTarget);

            var reason = button.data("reason");
            if (reason != "") $("#message").html(reason);
            else $("#message").html("No Message Yet.");
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form');
            const submitButton = document.getElementById('nin-request');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });

        $(document).ready(function() {
            hide();

            $("#service").change(function() {
                const selectedItem = this.value;

                // Clear dynamic content area
                $("#input-container").empty();
                $("#modify_lbl").text("").hide();

                let labelText = "";
                let inputs = '';

                switch (selectedItem) {
                    case '000':
                        // Requirements: NIN, Email
                        labelText = "Enter NIN and Email Address";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createInput('email', 'Email Address', 100, 'email');
                        break;

                    case '113':
                    case '114':
                        // Requirement: Only NIN
                        labelText = "Enter NIN Number";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        break;

                    case '001':
                        // Requirements: NIN, Tracking ID, Surname, First Name, Middle Name, DOB
                        labelText = "Full Identity Details";
                        inputs += createInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        // inputs += createInput('tracking_id', 'Tracking ID', '15', 'text',
                        //     '^(?=.*[a-zA-Z])(?=.*\\d)[a-zA-Z0-9]{15}$',
                        //     'Tracking ID must be 15 characters, containing letters and numbers');
                        inputs += createInput('surname', 'Surname');
                        inputs += createInput('firstname', 'First Name');
                        inputs += createInput('middlename', 'Middle Name', '', 'text', '',
                            '', '');
                        // inputs += createInput('dob', 'Date of Birth', '', 'date', '',
                        //     'Date of Birth is required!');
                        break;

                    default:
                        break;
                }

                $("#modify_lbl").text(labelText).show();
                $("#input-container").append(inputs);

                if (selectedItem === '115') {
                    const dobInput = document.getElementById('dob');

                    if (dobInput) {
                        dobInput.addEventListener('invalid', function() {
                            if (dobInput.validity.valueMissing) {
                                dobInput.setCustomValidity('Date of Birth is required!');
                            } else {
                                dobInput.setCustomValidity('');
                            }
                        });

                        dobInput.addEventListener('input', function() {
                            dobInput.setCustomValidity('');
                        });

                        // Optional: Prevent future dates
                        dobInput.max = new Date().toISOString().split("T")[0];
                    }
                }
            });
        });

        function hide() {
            $("#modify_lbl").hide();
        }

        function createInput(id, placeholder, maxlength = '', type = 'text', pattern = '', title = '', required =
            'required') {
            const max = maxlength ? `maxlength="${maxlength}"` : '';
            const pat = pattern ? `pattern="${pattern}"` : '';
            const tip = title ? `title="${title}"` : '';


            return `<input type="${type}" name="${id}" id="${id}" ${max} ${pat} ${tip} class="form-control mb-2" placeholder="${placeholder}" ${required} />`;
        }
    </script> --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const textarea = document.getElementById('bulkNins');
            const totalEl = document.getElementById('totalCount');
            const validEl = document.getElementById('validCount');
            const invalidEl = document.getElementById('invalidCount');
            const submitBtn = document.getElementById('bulkSubmitBtn');

            if (!textarea) return;

            function analyzeNins() {
                const lines = textarea.value
                    .split(/\r?\n/)
                    .map(v => v.trim())
                    .filter(v => v !== '');

                let valid = 0;
                let invalid = 0;

                lines.forEach(nin => {
                    /^\d{11}$/.test(nin) ? valid++ : invalid++;
                });

                totalEl.innerText = lines.length;
                validEl.innerText = valid;
                invalidEl.innerText = invalid;

                submitBtn.disabled = valid === 0;
            }

            textarea.addEventListener('input', analyzeNins);

            document.getElementById('bulkNinForm')
                .addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Submitting...';
                });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bulkTextarea = document.getElementById('bulkNins');
            if (!bulkTextarea) return;

            bulkTextarea.addEventListener('focus', function() {
                const bulkTab = document.getElementById('bulk-tab');
                if (bulkTab) bulkTab.click();
            });
        });
    </script>
@endpush
