@extends('layouts.dashboard')

@section('title', 'NIN Modification')

@push('styles')
    <style>
        .mobile-card {
            border-radius: 12px;
            border: 1px solid #edf2f7;
            margin-bottom: 1rem;
            transition: transform 0.2s;
        }

        .mobile-card:active {
            transform: scale(0.98);
        }

        .x-small {
            font-size: 0.75rem;
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
                        <h4 class="card-title">NIN Modification</h4>
                        <p class="card-description">Submit NIN Modification
                        <div class="row">

                            <div class="col-md-5 mb-3" role="tabpanel" aria-labelledby="new-tab">
                                @php
                                    $settings = \App\Models\SiteSetting::first();
                                @endphp

                                @if ($settings->nin_mod_enabled)
                                    <center>
                                        <img class="img-fluid" src="{{ asset('assets/images/img/nimc.png') }}"
                                            width="30%">
                                    </center>
                                    <center>
                                        <small class="font-italic text-danger"><i>
                                                <p class="text-muted mb-2">
                                                    Please note that this request will be processed within <strong>2–3
                                                        working
                                                        days</strong>. We appreciate your patience and will keep you updated
                                                    on
                                                    the status.
                                                </p>
                                                <p class="text-muted">
                                                    <strong class="text-danger">Date of Birth (DOB) modification
                                                        requests</strong> may take
                                                    <strong class="text-danger">4–5 working days</strong> to complete.
                                                </p>

                                                <p class="text-muted"> <span class="text-danger">Warning: </span> Submit
                                                    only
                                                    Fresh Jobs (NIN that has not been
                                                    modified before) Otherwise all Charges Incurred will not be Refunded.
                                                    DOB
                                                    Modification should be in range of 5yrs only. For above 5yrs range
                                                    contact
                                                    support.
                                                </p>
                                            </i>
                                        </small>
                                    </center>

                                    <div class="row text-center">
                                        <div class="col-md-12">
                                            <form id="form" name="nin-request" method="POST"
                                                action="{{ route('user.nin.services.mod') }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="row mb-2">
                                                    <div class="row">
                                                        <div class="col-md-12 mt-3 mb-3">
                                                            <select name="service" id="service"
                                                                class="form-select text-dark" required>
                                                                <option value="">-- Service Type --</option>
                                                                @foreach ($services as $service)
                                                                    <option value="{{ $service->service_code }}">
                                                                        {{ $service->name }} -
                                                                        &#x20A6;{{ number_format($service->amount, 2) }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <p class="mb-2 form-label" id="modify_lbl"></p>
                                                            <div id="input-container"></div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <center>
                                                                <button type="submit" id="nin-mod-request"
                                                                    class="btn text-light" style="background:#2563eb">
                                                                    <i class="las la-share"></i> Submit Request
                                                                </button>
                                                            </center>
                                                        </div>
                                                    </div>

                                                </div>

                                            </form>
                                        </div>

                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <div class="alert alert-danger border-0 mx-auto" style="max-width: 500px;">
                                            <i class="fas fa-toggle-off fa-2x text-dark mb-3"></i>
                                            <h5 class="alert-heading">NIN Modification Temporarily Disabled</h5>
                                            <p class="mb-0">The NIN modification service is currently unavailable. Please
                                                check back later or contact support for assistance.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-7" role="tabpanel" aria-labelledby="history-tab">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0 fw-bold">Recent Requests</h5>
                                </div>


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
                                                    <th width="5%" scope="col">ID</th>
                                                    <th scope="col">Reference No.</th>
                                                    <th scope="col">Service Type</th>
                                                    <th scope="col" class="text-center">Status</th>
                                                    <th scope="col" class="text-center">Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ninServices as $data)
                                                    <tr>
                                                        <th scope="row">
                                                            {{ ($ninServices->currentPage() - 1) * $ninServices->perPage() + $loop->iteration }}
                                                        </th>
                                                        <td>{{ $data->refno }}</td>
                                                        <td>{{ $data->description }}</td>
                                                        <td class="text-center">
                                                            @php
                                                                $badgeClass = match ($data->status) {
                                                                    'resolved', 'successful' => 'badge bg-success',
                                                                    'rejected', 'failed' => 'badge bg-danger',
                                                                    'processing', 'in-progress' => 'badge bg-primary',
                                                                    'query' => 'badge bg-warning text-dark',
                                                                    default => 'badge bg-warning',
                                                                };
                                                                $statusText = match ($data->status) {
                                                                    'resolved' => 'SUCCESSFUL',
                                                                    'query' => 'QUERIED',
                                                                    default => Str::upper($data->status),
                                                                };
                                                            @endphp
                                                            <span class="{{ $badgeClass }}">{{ $statusText }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="d-flex justify-content-center align-items-center">
                                                                <a type="button"
                                                                    class="me-2 btn btn-sm text-light py-1 px-2"
                                                                    style="background:#059669; font-size: 0.7rem;"
                                                                    data-bs-toggle="modal" data-id="2"
                                                                    data-reason="{{ $data->reason }}"
                                                                    data-docs="{{ $data->document }}"
                                                                    data-bs-target="#reason">
                                                                    Details


                                                                </a>
                                                                @if ($data->status === 'query')
                                                                    <a href="{{ route('user.nin-modification.edit', $data->id) }}"
                                                                        class="btn btn-sm text-light py-1 px-2"
                                                                        style="background:#059669; font-size: 0.7rem;">
                                                                        Edit
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </td>
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
                                                    'resolved', 'successful' => 'badge bg-success',
                                                    'rejected', 'failed' => 'badge bg-danger',
                                                    'processing', 'in-progress' => 'badge bg-primary',
                                                    'query' => 'badge bg-warning text-dark',
                                                    default => 'badge bg-warning',
                                                };
                                                $statusText = match ($data->status) {
                                                    'resolved' => 'SUCCESSFUL',
                                                    'query' => 'QUERIED',
                                                    default => Str::upper($data->status),
                                                };
                                            @endphp
                                            <div class="card mobile-card shadow-sm border-0">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <div class="small text-muted mb-1">
                                                                <i class="las la-hashtag"></i> {{ $data->refno }}
                                                            </div>
                                                            <h6 class="mb-0 fw-bold text-dark">{{ $data->description }}
                                                            </h6>
                                                        </div>
                                                        <span
                                                            class="{{ $badgeClass }} small">{{ $statusText }}</span>
                                                    </div>

                                                    @if ($data->reason)
                                                        <div
                                                            class="bg-light rounded p-2 my-2 border-start border-secondary border-3 x-small text-dark">
                                                            <strong>Admin Response:</strong> {!! $data->reason !!}
                                                        </div>
                                                    @endif

                                                    <div
                                                        class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                                        <small class="text-muted x-small">
                                                            <i class="las la-calendar"></i>
                                                            {{ $data->created_at->format('d M, Y h:i A') }}
                                                        </small>
                                                        <div class="d-flex align-items-center">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-dark px-2 me-1"
                                                                data-bs-toggle="modal" data-reason="{{ $data->reason }}"
                                                                data-docs="{{ $data->document }}"
                                                                data-bs-target="#reason">
                                                                Details
                                                            </button>
                                                            @if ($data->status === 'query')
                                                                <a href="{{ route('user.nin-modification.edit', $data->id) }}"
                                                                    class="btn btn-sm text-light px-2"
                                                                    style="background:#059669">
                                                                    Edit
                                                                </a>
                                                            @endif
                                                        </div>
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
                                    <center><img width="65%" src="{{ asset('assets/images/no-transaction.gif') }}"
                                            alt=""></center>
                                    <p class="text-center fw-semibold  fs-15"> No Request
                                        Available!</p>
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
                            <p><i class="bi bi-chat-dots-fill text-primary me-2"></i>Message</p>
                            <p id="message">No Message Yet.</p>
                            <p class="pt-2"></p>
                            <p><i class="bi bi-file-earmark-text-fill text-dark me-2"></i>Available Documents</p>
                            <p id="docs"></p>
                        </div>
                    </div>
                </div>
            </div>


            <div class="modal fade" id="consentModal" aria-labelledby="consentModalLabel" data-bs-keyboard="false"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
                    <div class="modal-content shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h6 class="modal-title" id="consentModalLabel">Consent & Authorization Agreement</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                            {!! $consent->nin_consent ?? '' !!}
                        </div>

                        <div class="modal-footer d-flex justify-content-between">
                            <button type="button" class="btn btn-danger" id="disagreeBtn" data-bs-dismiss="modal">I
                                Disagree</button>
                            <button type="button" class="btn btn-success" id="agreeBtn">I Agree</button>
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

            var docs = button.data("docs");

            if (reason != "") $("#message").html(reason);
            else $("#message").html("No Message Yet.");

            if (docs && docs !== "") {
                const url = `/storage/${docs}`;
                $("#docs").html(`
                <a href="${url}" download class="btn btn-sm btn-primary">
                     <i class="bi bi-download"></i> Download
                </a>`);
            } else {
                $("#docs").html(`<span class="text-muted">No documents uploaded.</span>`);
            }
        });

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
                $("#input-container").empty();
                $("#modify_lbl").text("").hide();

                let labelText = "";
                let inputs = '';

                switch (selectedItem) {

                    case '120':
                        labelText =
                            'Name Modification All fields marked (<span style="color:red">*</span>) are required';
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('surname', 'New Surname');
                        inputs += createLabeledInput('firstname', 'New First Name');
                        inputs += createLabeledInput('middlename', 'New Middle Name', '', '', '', '', '');
                        inputs += createLabeledInput('phone', 'Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');


                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>
                                    <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>
                                </div>
                            `;

                        break;

                    case '121':
                        labelText =
                            'Date of Birth Modification All fields marked(<span style="color: red ">*</span>) are required';
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('dob', 'New Date of Birth', '', 'date');
                        inputs += createLabeledInput('surname', 'Surname');
                        inputs += createLabeledInput('firstname', 'First Name');
                        inputs += createLabeledInput('middlename', 'Middle Name', '', '', '', '', '');
                        inputs += createLabeledInput('phone', 'Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');
                        inputs += createLabeledInput('full_address',
                            'Full Residential Address (Including State & LGA)', 500, 'text',
                            '', 'Full Residential Address (Including State & LGA');
                        inputs += createLabeledInput('origin_address',
                            'Origin Address (State & LGA)', 500, 'text',
                            '', 'Origin Address (State & LGA)');

                        inputs += '<hr><h5 class="text-center fw-bold">ATTESTATION DATA</h5>';
                        inputs += createLabeledInput('education_qualification', 'Education Qualification');
                        inputs += createLabeledInput('marital_status', 'Marital Status');

                        inputs += '<hr><h6 class="fw-bold">FATHER\'S DETAILS</h6>';
                        inputs += createLabeledInput('father_full_name', 'Father\'s Full Name');
                        inputs += createLabeledInput('father_state_of_origin', 'Father\'s State Of Origin');
                        inputs += createLabeledInput('father_lga_of_origin', 'Father\'s LGA Of Origin');

                        inputs += '<hr><h6 class="fw-bold">MOTHER\'S DETAILS</h6>';
                        inputs += createLabeledInput('mother_full_name', 'Mother\'s Full Name');
                        inputs += createLabeledInput('mother_state_of_origin', 'Mother\'s State Of Origin');
                        inputs += createLabeledInput('mother_lga_of_origin', 'Mother\'s LGA Of Origin');
                        inputs += createLabeledInput('mother_maiden_name', 'Mother\'s Maiden Name');

                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>
                                     <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>
                                </div>
                            `;
                        inputs += `
                                <div class="mb-3">
                                    <label for="affidavit" class="form-label">Upload Affidavit <span class="text-danger">*</span></label>
                                    <input
                                        type="file"
                                        id="affidavit"
                                        name="affidavit"
                                        accept=".pdf, .jpeg, .jpg, .png"
                                        class="form-control"
                                        required>
                                    </div> `;


                        break;

                    case '122':
                        labelText =
                            'Phone Number Modification All fields marked <span style="color: red "> * </span> are required)'
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('phone', 'New Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');

                        inputs += createLabeledInput('surname', 'Surname');
                        inputs += createLabeledInput('firstname', 'First Name');
                        inputs += createLabeledInput('middlename', 'Middle Name', '', '', '', '', '');

                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>
                                    <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>
                                </div>
                            `;
                        break;

                    case '123':
                        labelText =
                            'Address Modification All fields marked <span style="color: red"> (*) </span> are required';
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('address', 'New Residential Address');
                        inputs += createLabeledInput('phone', 'Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');

                        inputs += createLabeledInput('surname', 'Surname');
                        inputs += createLabeledInput('firstname', 'First Name');
                        inputs += createLabeledInput('middlename', 'Middle Name', '', '', '', '', '');
                        inputs += createLabeledInput('state', 'State');
                        inputs += createLabeledInput('lga', 'LGA');

                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>

                                    <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>

                                </div>
                            `;
                        break;
                    case '125':
                        labelText =
                            'Name and Date of Birth Modification All fields marked(<span style="color: red "> * </span>) are required';
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('dob', 'New Date of Birth', '', 'date');
                        inputs += createLabeledInput('surname', 'New Surname');
                        inputs += createLabeledInput('firstname', 'New First Name');
                        inputs += createLabeledInput('middlename', 'New Middle Name', '', '', '', '', '');
                        inputs += createLabeledInput('phone', 'Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');
                        inputs += createLabeledInput('full_address',
                            'Full Residential Address (Including State & LGA)', 500, 'text',
                            '', 'Full Residential Address (Including State & LGA');
                        inputs += createLabeledInput('origin_address',
                            'Origin Address (State & LGA)', 500, 'text',
                            '', 'Origin Address (State & LGA)');

                        inputs += '<hr><h5 class="text-center fw-bold">ATTESTATION DATA</h5>';
                        inputs += createLabeledInput('education_qualification', 'Education Qualification');
                        inputs += createLabeledInput('marital_status', 'Marital Status');

                        inputs += '<hr><h6 class="fw-bold">FATHER\'S DETAILS</h6>';
                        inputs += createLabeledInput('father_full_name', 'Father\'s Full Name');
                        inputs += createLabeledInput('father_state_of_origin', 'Father\'s State Of Origin');
                        inputs += createLabeledInput('father_lga_of_origin', 'Father\'s LGA Of Origin');

                        inputs += '<hr><h6 class="fw-bold">MOTHER\'S DETAILS</h6>';
                        inputs += createLabeledInput('mother_full_name', 'Mother\'s Full Name');
                        inputs += createLabeledInput('mother_state_of_origin', 'Mother\'s State Of Origin');
                        inputs += createLabeledInput('mother_lga_of_origin', 'Mother\'s LGA Of Origin');
                        inputs += createLabeledInput('mother_maiden_name', 'Mother\'s Maiden Name');

                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>
                                     <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>
                                </div>
                            `;
                        inputs += `
                                <div class="mb-3">
                                    <label for="affidavit" class="form-label">Upload Affidavit <span class="text-danger">*</span></label>
                                    <input
                                        type="file"
                                        id="affidavit"
                                        name="affidavit"
                                        accept=".pdf, .jpeg, .jpg, .png"
                                        class="form-control"
                                        required>
                                    </div> `;
                        break;

                    case '126':
                        labelText =
                            'Phone Number and Date of Birth Modification All fields marked(<span style="color: red "> (*) </span>) are required';
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('dob', 'New Date of Birth', '', 'date');
                        inputs += createLabeledInput('phone', 'New Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');
                        inputs += createLabeledInput('surname', 'Surname');
                        inputs += createLabeledInput('firstname', 'First Name');
                        inputs += createLabeledInput('middlename', 'Middle Name', '', '', '', '', '');

                        inputs += createLabeledInput('full_address',
                            'Full Residential Address (Including State & LGA)', 500, 'text',
                            '', 'Full Residential Address (Including State & LGA');
                        inputs += createLabeledInput('origin_address', 'Origin Address (State & LGA)', 500,
                            'text', '', 'Origin Address (State & LGA)');

                        inputs += '<hr><h5 class="text-center fw-bold">ATTESTATION DATA</h5>';
                        inputs += createLabeledInput('education_qualification', 'Education Qualification');
                        inputs += createLabeledInput('marital_status', 'Marital Status');

                        inputs += '<hr><h6 class="fw-bold">FATHER\'S DETAILS</h6>';
                        inputs += createLabeledInput('father_full_name', 'Father\'s Full Name');
                        inputs += createLabeledInput('father_state_of_origin', 'Father\'s State Of Origin');
                        inputs += createLabeledInput('father_lga_of_origin', 'Father\'s LGA Of Origin');

                        inputs += '<hr><h6 class="fw-bold">MOTHER\'S DETAILS</h6>';
                        inputs += createLabeledInput('mother_full_name', 'Mother\'s Full Name');
                        inputs += createLabeledInput('mother_state_of_origin', 'Mother\'s State Of Origin');
                        inputs += createLabeledInput('mother_lga_of_origin', 'Mother\'s LGA Of Origin');
                        inputs += createLabeledInput('mother_maiden_name', 'Mother\'s Maiden Name');

                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>
                                     <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>
                                </div>
                            `;
                        inputs += `
                                <div class="mb-3">
                                    <label for="affidavit" class="form-label">Upload Affidavit <span class="text-danger">*</span></label>
                                    <input
                                        type="file"
                                        id="affidavit"
                                        name="affidavit"
                                        accept=".pdf, .jpeg, .jpg, .png"
                                        class="form-control"
                                        required>
                                    </div> `;
                        break;
                    case '127':
                        labelText =
                            'Name and Phone Number All fields marked <span style="color:red"> (*) </span> are required)';
                        inputs += createLabeledInput('nin', 'NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createLabeledInput('surname', 'New Surname');
                        inputs += createLabeledInput('firstname', 'New First Name');
                        inputs += createLabeledInput('middlename', 'New Middle Name', '', '', '', '', '');
                        inputs += createLabeledInput('phone', 'New Phone Number', 11, 'text',
                            '^\\d{11}$', 'Enter a valid 11-digit phone number');


                        inputs += `
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Upload Passport Photo <span class="text-danger">*</span></label>
                                    <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewImage(event)" required>
                                    <div class="mt-2 text-center">
                                        <img id="imagePreview" src="" alt="Image Preview"
                                            style="max-height: 150px; display: none;"
                                            class="img-thumbnail mx-auto d-block">
                                    </div>
                                </div>
                            `;

                        break;

                    default:
                        break;
                }

                $("#modify_lbl").html(labelText).show();
                $("#input-container").append(inputs);
            });
        });

        function hide() {
            $("#modify_lbl").hide();
        }

        function createLabeledInput(id, label, maxlength = '', type = 'text', pattern = '', title = '', required = true) {
            const max = maxlength ? `maxlength="${maxlength}"` : '';
            const pat = pattern ? `pattern="${pattern}"` : '';
            const tip = title ? `title="${title}"` : '';
            const req = required ? 'required' : '';

            let reqField = "<span style='color: red '>*</span>";

            if (id == 'middlename')
                reqField = '';

            return `
            <div class="mb-3">
                <label for="${id}" class="form-label">${label} ${reqField}</label>
                <input type="${type}" name="${id}" id="${id}" class="form-control" placeholder="${label}" ${max} ${pat} ${tip} ${req} />
            </div>
        `;
        }

        function createFileInput(id, label) {
            return `
            <div class="mb-3">
                <label for="${id}" class="form-label">${label}</label>
                <input type="file" name="${id}" id="${id}" class="form-control" accept="image/*" required />
            </div>
        `;
        }
    </script>
    <script>
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('imagePreview');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
                preview.style.display = 'none';
            }
        }
    </script>
@endpush
