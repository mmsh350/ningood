@extends('layouts.dashboard')

@section('title', 'CAC Business Name Registration')

@section('content')
    <div class="row">
        <div class="col-12 mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">

                    {{-- Alerts --}}
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- CAC Logo + Service Fee --}}
                    <div class="d-flex justify-content-between align-items-center my-3">
                        <div>
                            <h4 class="fw-bold mb-0">CAC Business Name Registration</h4>
                        </div>

                        {{-- Logo placeholder --}}
                        <div>
                            <img src="{{ asset('assets/images/img/icon/cac.png') }}" alt="CAC Logo" height="60">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5 class="mb-1">Service Fee: ₦{{ number_format($ServiceFee, 2) }}</h5>
                        <small>This fee covers CAC business name reservation and registration processing.</small>
                    </div>

                    {{-- TABS --}}
                    <ul class="nav nav-tabs mb-3" id="businessTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#formTab"
                                type="button" role="tab">
                                Registration Form
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#historyTab"
                                type="button" role="tab">
                                Submission History
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="businessTabsContent">

                        {{-- =================== FORM TAB =================== --}}
                        <div class="tab-pane fade show active" id="formTab" role="tabpanel">

                            <form method="POST" action="{{ route('user.business.store') }}" enctype="multipart/form-data">
                                @csrf

                                {{-- PERSONAL INFO --}}
                                <h5 class="mt-3 mb-2">Personal Information <span class="text-danger">(*)</span></h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Surname</label>
                                        <input type="text" name="surname" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Other Name</label>
                                        <input type="text" name="other_name" class="form-control">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" name="date_of_birth" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select text-dark" required>
                                            <option value="">Select Gender</option>
                                            <option>Male</option>
                                            <option>Female</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone_number" maxlength="11" class="form-control"
                                            required>
                                    </div>
                                </div>

                                {{-- RESIDENTIAL ADDRESS --}}
                                <h5 class="mt-4 mb-2">Residential Address <span class="text-danger">(*)</span></h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="res_state" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">LGA</label>
                                        <input type="text" name="res_lga" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">City/Town/Village</label>
                                        <input type="text" name="res_city" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">House Number</label>
                                        <input type="text" name="res_house_number" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Street Name</label>
                                        <input type="text" name="res_street_name" class="form-control" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Description of House</label>
                                        <textarea name="res_description" class="form-control" rows="2" required></textarea>
                                    </div>
                                </div>
                                {{-- BUSINESS INFO --}}
                                <h5 class="mt-4 mb-2">Business Information <span class="text-danger">(*)</span></h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Nature of Business</label>
                                        <input type="text" name="nature_of_business" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Business Name 1</label>
                                        <input type="text" name="business_name_1" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Business Name 2</label>
                                        <input type="text" name="business_name_2" class="form-control" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Functional Email Address</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                </div>

                                {{-- BUSINESS ADDRESS --}}
                                <h5 class="mt-4 mb-2">Business Address <span class="text-danger">(*)</span></h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" name="bus_state" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">LGA</label>
                                        <input type="text" name="bus_lga" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">City/Town/Village</label>
                                        <input type="text" name="bus_city" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">House Number</label>
                                        <input type="text" name="bus_house_number" class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Street Name</label>
                                        <input type="text" name="bus_street_name" class="form-control" required>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label">Description of Business Address</label>
                                        <textarea name="bus_description" class="form-control" rows="2" required></textarea>
                                    </div>
                                </div>



                                {{-- UPLOADS --}}
                                <h5 class="mt-4 mb-2">Upload Supporting Documents <span class="text-danger">(*)</span>
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">NIN</label>
                                        <input type="file" name="nin" accept=".png,.jpg,.jpeg"
                                            class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Signature</label>
                                        <input type="file" name="signature" accept=".png,.jpg,.jpeg"
                                            class="form-control" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Passport</label>
                                        <input type="file" name="passport" accept=".png,.jpg,.jpeg"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-4">Submit Registration</button>
                                </div>
                            </form>

                        </div>

                        {{-- =================== HISTORY TAB =================== --}}
                        <div class="tab-pane fade" id="historyTab" role="tabpanel">

                            <h5 class="mt-3 mb-3">Your Submission History</h5>

                            @if ($submissions->count())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Business Name</th>
                                                <th>Status</th>
                                                <th>Details</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($submissions as $submission)
                                                <tr>
                                                    <td>{{ $submission->created_at->format('d M, Y') }}</td>
                                                    <td>{{ $submission->business_name_1 }}</td>
                                                    <td>
                                                        @if ($submission->status == 'pending')
                                                            <span class="badge bg-warning">Pending</span>
                                                        @elseif($submission->status == 'completed')
                                                            <span class="badge bg-success">Completed</span>
                                                        @elseif($submission->status == 'processing')
                                                            <span class="badge bg-primary">Processing</span>
                                                        @elseif($submission->status == 'query')
                                                            <span class="badge bg-info">Queried</span>
                                                        @else
                                                            <span class="badge bg-danger">Failed</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                                data-bs-target="#submissionModal{{ $submission->id }}">
                                                                View
                                                            </button>
                                                            @if ($submission->status == 'query')
                                                                <a href="{{ route('user.business.edit', $submission->id) }}"
                                                                    class="btn btn-sm btn-info text-white">
                                                                    Edit
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>

                                                <div class="modal fade" id="submissionModal{{ $submission->id }}"
                                                    tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-scrollable modal-md">
                                                        <div class="modal-content shadow-lg">
                                                            <div class="modal-header bg-primary text-white">
                                                                <h5 class="modal-title"><i
                                                                        class="bi bi-file-text-fill me-2"></i>Submission
                                                                    Details</h5>
                                                                <button type="button" class="btn-close btn-close-white"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">

                                                                {{-- Personal Information --}}
                                                                <div class="mb-3 p-3 border rounded bg-light text-dark">
                                                                    <h6 class="fw-bold text-secondary text-dark"><i
                                                                            class="bi bi-person-fill me-1 "></i>Personal
                                                                        Information</h6>
                                                                    <div class="row">
                                                                        <div class="col-md-6"><strong>Surname:</strong>
                                                                            {{ $submission->surname }}</div>
                                                                        <div class="col-md-6"><strong>First Name:</strong>
                                                                            {{ $submission->first_name }}</div>
                                                                        <div class="col-md-6"><strong>Other Name:</strong>
                                                                            {{ $submission->other_name ?? '-' }}</div>
                                                                        <div class="col-md-6"><strong>Date of
                                                                                Birth:</strong>
                                                                            {{ $submission->date_of_birth }}</div>
                                                                        <div class="col-md-6"><strong>Gender:</strong>
                                                                            {{ $submission->gender }}</div>
                                                                        <div class="col-md-6"><strong>Phone:</strong>
                                                                            {{ $submission->phone_number }}</div>
                                                                    </div>
                                                                </div>

                                                                {{-- Residential Address --}}
                                                                <div class="mb-3 p-3 border rounded bg-light">
                                                                    <h6 class="fw-bold text-secondary text-dark"><i
                                                                            class="bi bi-house-fill me-1 text-dark"></i>Residential
                                                                        Address</h6>
                                                                    <p class="mb-1">{{ $submission->res_state }},
                                                                        {{ $submission->res_lga }},
                                                                        {{ $submission->res_city }}</p>
                                                                    <p class="mb-1">House:
                                                                        {{ $submission->res_house_number ?? '-' }}, Street:
                                                                        {{ $submission->res_street_name ?? '-' }}</p>
                                                                    <p class="mb-0"><strong>Description of House
                                                                            Addrees:</strong>
                                                                        {{ $submission->res_description ?? '-' }}</p>
                                                                </div>


                                                                {{-- Business Information --}}
                                                                <div class="mb-3 p-3 border rounded bg-light">
                                                                    <h6 class="fw-bold text-secondary text-dark"><i
                                                                            class="bi bi-briefcase-fill me-1 text-dark"></i>Business
                                                                        Information</h6>
                                                                    <p class="mb-1"><strong>Nature:</strong>
                                                                        {{ $submission->nature_of_business }}</p>
                                                                    <p class="mb-1"><strong>Business Name 1:</strong>
                                                                        {{ $submission->business_name_1 }}</p>
                                                                    <p class="mb-1"><strong>Business Name 2:</strong>
                                                                        {{ $submission->business_name_2 ?? '-' }}</p>
                                                                    <p class="mb-0"><strong>Email:</strong>
                                                                        {{ $submission->email }}</p>
                                                                </div>


                                                                {{-- Business Address --}}
                                                                <div class="mb-3 p-3 border rounded bg-light">
                                                                    <h6 class="fw-bold text-secondary text-dark"><i
                                                                            class="bi bi-building me-1 text-dark"></i>Business
                                                                        Address</h6>
                                                                    <p class="mb-1">{{ $submission->bus_state }},
                                                                        {{ $submission->bus_lga }},
                                                                        {{ $submission->bus_city }}</p>
                                                                    <p class="mb-1">House:
                                                                        {{ $submission->bus_house_number ?? '-' }}, Street:
                                                                        {{ $submission->bus_street_name ?? '-' }}</p>
                                                                    <p class="mb-0"><strong>Description of Business
                                                                            Address:</strong>
                                                                        {{ $submission->bus_description ?? '-' }}</p>
                                                                </div>

                                                                {{-- Uploaded Documents --}}
                                                                <div class="mb-3 p-3 border rounded bg-light">
                                                                    <h6 class="fw-bold text-secondary text-dark"><i
                                                                            class="bi bi-file-earmark-arrow-up-fill me-1 text-dark"></i>Uploaded
                                                                        Documents</h6>
                                                                    <p class="mb-1"><strong>NIN:</strong>
                                                                        @if ($submission->nin_path)
                                                                            <a href="{{ asset('storage/' . $submission->nin_path) }}"
                                                                                target="_blank"
                                                                                class="link-primary">View</a>
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </p>
                                                                    <p class="mb-1"><strong>Signature:</strong>
                                                                        @if ($submission->signature_path)
                                                                            <a href="{{ asset('storage/' . $submission->signature_path) }}"
                                                                                target="_blank"
                                                                                class="link-primary">View</a>
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </p>
                                                                    <p class="mb-0"><strong>Passport:</strong>
                                                                        @if ($submission->passport_path)
                                                                            <a href="{{ asset('storage/' . $submission->passport_path) }}"
                                                                                target="_blank"
                                                                                class="link-primary">View</a>
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </p>
                                                                </div>

                                                                {{-- Status & Response --}}
                                                                <div class="p-3 border rounded bg-light">
                                                                    <h6 class="fw-bold text-secondary text-dark"><i
                                                                            class="bi bi-info-circle-fill me-1 text-dark"></i>Status
                                                                        & Response</h6>
                                                                    <p class="mb-1 mb-3"><strong>Status:</strong>
                                                                        @if ($submission->status == 'pending')
                                                                            <span
                                                                                class="badge bg-warning text-dark">Pending</span>
                                                                        @elseif($submission->status == 'completed')
                                                                            <span class="badge bg-success">Completed</span>
                                                                        @elseif($submission->status == 'processing')
                                                                            <span
                                                                                class="badge bg-primary">Processing</span>
                                                                        @elseif($submission->status == 'query')
                                                                            <span class="badge bg-info">Queried</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Failed</span>
                                                                        @endif
                                                                    </p>
                                                                    <p class="mb-0 mb-3"><strong>Response:</strong>
                                                                        {!! $submission->response ?? '-' !!}</p>



                                                                    <p class="mb-0"><strong>Response Documents:</strong>
                                                                        @if ($submission->response_documents)
                                                                            @php
                                                                                $docs = json_decode(
                                                                                    $submission->response_documents,
                                                                                    true,
                                                                                );
                                                                            @endphp

                                                                            @if (!empty($docs))
                                                                                <ul class="mb-0 ps-3">
                                                                                    @foreach ($docs as $doc)
                                                                                        <li>
                                                                                            <a href="{{ asset('storage/' . $doc) }}"
                                                                                                target="_blank"
                                                                                                class="link-primary">
                                                                                                {{ basename($doc) }}
                                                                                            </a>
                                                                                        </li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            @else
                                                                                -
                                                                            @endif
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </p>

                                                                </div>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal"><i
                                                                        class="bi bi-x-circle me-1"></i>Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- PAGINATION --}}
                                <div class="mt-3">
                                    {{ $submissions->links() }}
                                </div>
                            @else
                                <p class="text-muted">No submissions found.</p>
                            @endif

                        </div>
                    </div> {{-- END TAB CONTENT --}}
                </div>
            </div>
        </div>
    </div>
@endsection
