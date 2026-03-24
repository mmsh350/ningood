@extends('layouts.dashboard')

@section('title', 'Edit BVN Modification Request')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Edit Request - {{ $modRequest->refno }}</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card border-0 shadow-sm mb-4" style="background: #fbf9ff; border-radius: 10px;">
                        <div class="card-header border-0 bg-transparent pt-3 pb-0">
                            <h6 class="fw-bold mb-0" style="color: #2563eb;">
                                <i class="las la-comment-dots me-1 fs-5"></i>
                                Administrator Feedback
                            </h6>
                            <hr class="mt-2 mb-0" style="width: 50px; border-top: 2px solid #2563eb; opacity: 1;">
                        </div>
                        <div class="card-body p-4 pt-3">
                            <div class="p-3 bg-white rounded border border-light-purple shadow-none"
                                style="border: 1px solid #e0d4f0 !important;">
                                <div class="text-dark small" style="line-height: 1.6;">
                                    {!! $modRequest->reason ?? 'Please review your submission and make the necessary corrections.' !!}
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted small">
                                <i class="las la-info-circle me-1"></i>
                                Please only update the <strong>queried fields</strong> as specified in
                                the admin feedback above. Unnecessary changes to other fields may delay processing.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('user.bvn-modification.update', $modRequest->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Bank</label>
                                <input type="text" class="form-control" value="{{ $modRequest->bank->name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Service</label>
                                <input type="text" class="form-control" value="{{ $modRequest->service->name }}"
                                    readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="bvn_no" class="form-label fw-semibold">BVN Number</label>
                                <input type="text" id="bvn_no" name="bvn_no" maxlength="11" class="form-control"
                                    value="{{ $modRequest->bvn_no }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nin_number" class="form-label fw-semibold">NIN Number</label>
                                <input type="text" id="nin_number" name="nin_number" maxlength="11" class="form-control"
                                    value="{{ $modRequest->nin_number }}" required>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Current Details</h6>
                                @php
                                    $current = $modRequest->modification_data['current_data'] ?? [];
                                @endphp
                                <div class="mb-2">
                                    <label class="form-label small">First Name</label>
                                    <input type="text" name="current_firstname" class="form-control"
                                        value="{{ $current['First Name'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Middle Name</label>
                                    <input type="text" name="current_middlename" class="form-control"
                                        value="{{ $current['Middle Name'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Surname</label>
                                    <input type="text" name="current_surname" class="form-control"
                                        value="{{ $current['Surname'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Date of Birth</label>
                                    <input type="date" name="current_dob" class="form-control"
                                        value="{{ $current['Date of Birth'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Phone Number</label>
                                    <input type="text" name="current_phone" class="form-control"
                                        value="{{ $current['Phone Number'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Gender</label>
                                    <select name="current_gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="Male"
                                            {{ ($current['Gender'] ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female"
                                            {{ ($current['Gender'] ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address</label>
                                    <textarea name="current_address" class="form-control" rows="2">{{ $current['Address'] ?? '' }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3 text-primary">New Details</h6>
                                @php
                                    $new = $modRequest->modification_data['new_data'] ?? [];
                                @endphp
                                <div class="mb-2">
                                    <label class="form-label small">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="new_firstname" class="form-control"
                                        value="{{ $new['First Name'] ?? '' }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Middle Name</label>
                                    <input type="text" name="new_middlename" class="form-control"
                                        value="{{ $new['Middle Name'] ?? '' }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="new_surname" class="form-control"
                                        value="{{ $new['Surname'] ?? '' }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Date of Birth <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="new_dob" class="form-control"
                                        value="{{ $new['Date of Birth'] ?? '' }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="new_phone" class="form-control"
                                        value="{{ $new['Phone Number'] ?? '' }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Gender <span class="text-danger">*</span></label>
                                    <select name="new_gender" class="form-select" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ ($new['Gender'] ?? '') == 'Male' ? 'selected' : '' }}>
                                            Male</option>
                                        <option value="Female" {{ ($new['Gender'] ?? '') == 'Female' ? 'selected' : '' }}>
                                            Female</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Address <span class="text-danger">*</span></label>
                                    <textarea name="new_address" class="form-control" required rows="2">{{ $new['Address'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn   text-light w-100" style="background:#2563eb">Update and
                                Resubmit
                                Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
