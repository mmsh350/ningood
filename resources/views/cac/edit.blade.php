@extends('layouts.dashboard')

@section('title', 'Edit CAC Company Registration')

@section('content')
    <div class="row justify-content-center">
        <div class="col-12">
            {{-- Alerts --}}
            @if (session('success'))
                <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show">
                    <i class="las la-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show">
                    <i class="las la-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="mb-1 fw-bold">Edit Company Registration</h4>
                </div>

                <div class="card-body p-4 pt-0">

                    {{-- Administrator Feedback --}}
                    <div class="card border-0 shadow-sm mb-4" style="background: #fbf9ff; border-radius: 10px;">
                        <div class="card-header border-0 bg-transparent pt-3 pb-0">
                            <h6 class="fw-bold mb-0 text-info">

                                Administrator Feedback
                            </h6>
                            <hr class="mt-2 mb-0" style="width: 50px; border-top: 2px solid #0dcaf0; opacity: 1;">
                        </div>
                        <div class="card-body p-4 pt-3 text-dark">
                            <div class="p-3 bg-white rounded border border-info shadow-none">
                                <div class="small" style="line-height: 1.6;">
                                    {!! $registration->admin_comment ?? 'Please review your submission and make the necessary corrections.' !!}
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted small">
                                <i class="las la-info-circle me-1"></i>
                                Please only update the <strong>queried fields</strong> as specified in the feedback above.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('user.company.update', $registration->id) }}"
                        enctype="multipart/form-data" id="cacEditForm">
                        @csrf
                        @method('PUT')

                        {{-- 1. DIRECTOR INFORMATION --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">1</div>
                                <h5 class="mb-0 fw-bold text-dark">Director Information</h5>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Surname <span class="text-danger">*</span></label>
                                    <input type="text" name="director_surname" class="form-control"
                                        value="{{ $registration->director_surname }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="director_firstname" class="form-control"
                                        value="{{ $registration->director_firstname }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Other Name</label>
                                    <input type="text" name="director_othername" class="form-control"
                                        value="{{ $registration->director_othername }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Date of Birth <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="director_dob" class="form-control"
                                        value="{{ $registration->director_dob ? $registration->director_dob->format('Y-m-d') : '' }}"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                    <select name="director_gender" class="form-select text-dark" required>
                                        <option value="">Select Gender</option>
                                        <option {{ $registration->director_gender == 'Male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option {{ $registration->director_gender == 'Female' ? 'selected' : '' }}>Female
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="director_email" class="form-control"
                                        value="{{ $registration->director_email }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Phone <span class="text-danger">*</span></label>
                                    <input type="text" maxlength="11" name="director_phone" class="form-control"
                                        value="{{ $registration->director_phone }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">NIN (National Identity Number) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="director_nin" maxlength="11" class="form-control"
                                        value="{{ $registration->director_nin }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- 2. RESIDENTIAL ADDRESS --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">2</div>
                                <h5 class="mb-0 fw-bold text-dark">Residential Address</h5>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="res_state" class="form-control"
                                        value="{{ $registration->res_state }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">LGA <span class="text-danger">*</span></label>
                                    <input type="text" name="res_lga" class="form-control"
                                        value="{{ $registration->res_lga }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City/Town/Village <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="res_city" class="form-control"
                                        value="{{ $registration->res_city }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">House Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="res_house_number" class="form-control"
                                        value="{{ $registration->res_house_number }}" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Street Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="res_street_name" class="form-control"
                                        value="{{ $registration->res_street_name }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Description of Address <span
                                            class="text-danger">*</span></label>
                                    <textarea name="res_description" class="form-control" rows="2" required>{{ $registration->res_description }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 3. BUSINESS ADDRESS --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">3</div>
                                <h5 class="mb-0 fw-bold text-dark">Business Address</h5>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State <span class="text-danger">*</span></label>
                                    <input type="text" name="bus_state" class="form-control"
                                        value="{{ $registration->bus_state }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">LGA <span class="text-danger">*</span></label>
                                    <input type="text" name="bus_lga" class="form-control"
                                        value="{{ $registration->bus_lga }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City/Town/Village <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="bus_city" class="form-control"
                                        value="{{ $registration->bus_city }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">House Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="bus_house_number" class="form-control"
                                        value="{{ $registration->bus_house_number }}" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold">Street Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="bus_street_name" class="form-control"
                                        value="{{ $registration->bus_street_name }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Description of Business Address <span
                                            class="text-danger">*</span></label>
                                    <textarea name="bus_description" class="form-control" rows="2" required>{{ $registration->bus_description }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 4. BUSINESS INFORMATION --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">4</div>
                                <h5 class="mb-0 fw-bold text-dark">Business Details</h5>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Nature of Business <span
                                            class="text-danger">*</span></label>
                                    <textarea name="nature_of_business" class="form-control" rows="2" required>{{ $registration->nature_of_business }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Business Name Option 1 <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="business_name_1" class="form-control"
                                        value="{{ $registration->business_name_1 }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Business Name Option 2 <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="business_name_2" class="form-control"
                                        value="{{ $registration->business_name_2 }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Functional Business Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="business_email" class="form-control"
                                        value="{{ $registration->business_email }}" required>
                                </div>
                            </div>
                        </div>

                        {{-- 5. WITNESS INFORMATION --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">5</div>
                                <h5 class="mb-0 fw-bold text-dark">Witness Information</h5>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Surname <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="witness_surname" class="form-control"
                                        value="{{ $registration->witness_surname }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="witness_firstname" class="form-control"
                                        value="{{ $registration->witness_firstname }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Other Name</label>
                                    <input type="text" name="witness_othername" class="form-control"
                                        value="{{ $registration->witness_othername }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="witness_phone" class="form-control"
                                        value="{{ $registration->witness_phone }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="witness_email" class="form-control"
                                        value="{{ $registration->witness_email }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">NIN Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="witness_nin" maxlength="11" class="form-control"
                                        value="{{ $registration->witness_nin }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Witness Home Address <span
                                            class="text-danger">*</span></label>
                                    <textarea name="witness_address" class="form-control" rows="2" required>{{ $registration->witness_address }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 6. SHAREHOLDER INFORMATION --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">6</div>
                                <h5 class="mb-0 fw-bold text-dark">Shareholder Information</h5>
                            </div>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Surname <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="shareholder_surname" class="form-control"
                                        value="{{ $registration->shareholder_surname }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="shareholder_firstname" class="form-control"
                                        value="{{ $registration->shareholder_firstname }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Other Name</label>
                                    <input type="text" name="shareholder_othername" class="form-control"
                                        value="{{ $registration->shareholder_othername }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Date of Birth <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="shareholder_dob" class="form-control"
                                        value="{{ $registration->shareholder_dob ? $registration->shareholder_dob->format('Y-m-d') : '' }}"
                                        required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Gender <span
                                            class="text-danger">*</span></label>
                                    <select name="shareholder_gender" class="form-select text-dark" required>
                                        <option value="">Select Gender</option>
                                        <option {{ $registration->shareholder_gender == 'Male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option {{ $registration->shareholder_gender == 'Female' ? 'selected' : '' }}>
                                            Female</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Nationality <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="shareholder_nationality" class="form-control"
                                        value="{{ $registration->shareholder_nationality }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Phone Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" maxlength="11" name="shareholder_phone" class="form-control"
                                        value="{{ $registration->shareholder_phone }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="shareholder_email" class="form-control"
                                        value="{{ $registration->shareholder_email }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">NIN Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="shareholder_nin" maxlength="11" class="form-control"
                                        value="{{ $registration->shareholder_nin }}" required>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Shareholder Home Address <span
                                            class="text-danger">*</span></label>
                                    <textarea name="shareholder_address" class="form-control" rows="2" required>{{ $registration->shareholder_address }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 7. UPLOADS --}}
                        <div class="form-section mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="section-number me-3 text-white">7</div>
                                <h5 class="mb-0 fw-bold text-dark">Update Documents <small
                                        class="text-muted">(Optional)</small></h5>
                            </div>
                            <div class="row g-4 text-dark">
                                <div class="col-md-4">
                                    <div class="upload-wrapper p-3 border rounded text-center">
                                        <label class="form-label fw-bold d-block mb-3">Signature of Director</label>
                                        <input type="file" name="director_signature" class="form-control"
                                            accept="image/*">
                                        @if ($registration->director_signature_path)
                                            <small class="text-muted mt-2 d-block">Current: <a
                                                    href="{{ asset('storage/' . $registration->director_signature_path) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="upload-wrapper p-3 border rounded text-center">
                                        <label class="form-label fw-bold d-block mb-3">Signature of Witness</label>
                                        <input type="file" name="witness_signature" class="form-control"
                                            accept="image/*">
                                        @if ($registration->witness_signature_path)
                                            <small class="text-muted mt-2 d-block">Current: <a
                                                    href="{{ asset('storage/' . $registration->witness_signature_path) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="upload-wrapper p-3 border rounded text-center">
                                        <label class="form-label fw-bold d-block mb-3">Signature of Shareholder</label>
                                        <input type="file" name="shareholder_signature" class="form-control"
                                            accept="image/*">
                                        @if ($registration->shareholder_signature_path)
                                            <small class="text-muted mt-2 d-block">Current: <a
                                                    href="{{ asset('storage/' . $registration->shareholder_signature_path) }}"
                                                    target="_blank">View</a></small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-5">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold"
                                style="border-radius: 30px;">
                                Update & Resubmit Registration
                            </button>
                            <a href="{{ route('user.company.create') }}" class="btn btn-light btn-lg px-5 shadow-sm ms-2"
                                style="border-radius: 30px;">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .section-number {
                width: 35px;
                height: 35px;
                background: #482666;
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                box-shadow: 0 4px 10px rgba(72, 38, 102, 0.2);
            }

            .form-control,
            .form-select {
                border-radius: 8px;
                padding: 10px 15px;
                border: 1px solid #e2e8f0;
                background: #fcfcfd;
            }

            .form-control:focus {
                box-shadow: 0 0 0 3px rgba(72, 38, 102, 0.1);
                border-color: #482666;
            }

            .upload-wrapper {
                transition: all 0.3s;
                cursor: pointer;
            }

            .upload-wrapper:hover {
                background: #f8fafc;
                border-color: #482666 !important;
            }
        </style>
    @endpush
@endsection
