@extends('layouts.dashboard')

@section('title', 'Edit Business Name Registration')

@section('content')
    <div class="row">
        <div class="col-12 mb-3 mt-1">
            <h4 class="mb-1">Edit Registration 👋</h4>
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
                                    {!! $registration->response ?? 'Please review your submission and make the necessary corrections.' !!}
                                </div>
                            </div>
                            <p class="mt-3 mb-0 text-muted small">
                                <i class="bi bi-info-circle me-1"></i>
                                Please only update the <strong>queried fields</strong> as specified in the feedback above.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('user.business.update', $registration->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- PERSONAL INFO --}}
                        <h5 class="mt-3 mb-2">Personal Information <span class="text-danger">(*)</span></h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Surname</label>
                                <input type="text" name="surname" class="form-control"
                                    value="{{ $registration->surname }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control"
                                    value="{{ $registration->first_name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Other Name</label>
                                <input type="text" name="other_name" class="form-control"
                                    value="{{ $registration->other_name }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control"
                                    value="{{ $registration->date_of_birth }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select text-dark" required>
                                    <option value="">Select Gender</option>
                                    <option {{ $registration->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option {{ $registration->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" maxlength="11" class="form-control"
                                    value="{{ $registration->phone_number }}" required>
                            </div>
                        </div>

                        {{-- RESIDENTIAL ADDRESS --}}
                        <h5 class="mt-4 mb-2">Residential Address <span class="text-danger">(*)</span></h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="res_state" class="form-control"
                                    value="{{ $registration->res_state }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">LGA</label>
                                <input type="text" name="res_lga" class="form-control"
                                    value="{{ $registration->res_lga }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">City/Town/Village</label>
                                <input type="text" name="res_city" class="form-control"
                                    value="{{ $registration->res_city }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">House Number</label>
                                <input type="text" name="res_house_number" class="form-control"
                                    value="{{ $registration->res_house_number }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Street Name</label>
                                <input type="text" name="res_street_name" class="form-control"
                                    value="{{ $registration->res_street_name }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description of House</label>
                                <textarea name="res_description" class="form-control" rows="2" required>{{ $registration->res_description }}</textarea>
                            </div>
                        </div>

                        {{-- BUSINESS INFO --}}
                        <h5 class="mt-4 mb-2">Business Information <span class="text-danger">(*)</span></h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nature of Business</label>
                                <input type="text" name="nature_of_business" class="form-control"
                                    value="{{ $registration->nature_of_business }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Business Name 1</label>
                                <input type="text" name="business_name_1" class="form-control"
                                    value="{{ $registration->business_name_1 }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Business Name 2</label>
                                <input type="text" name="business_name_2" class="form-control"
                                    value="{{ $registration->business_name_2 }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Functional Email Address</label>
                                <input type="email" name="email" class="form-control"
                                    value="{{ $registration->email }}" required>
                            </div>
                        </div>

                        {{-- BUSINESS ADDRESS --}}
                        <h5 class="mt-4 mb-2">Business Address <span class="text-danger">(*)</span></h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="bus_state" class="form-control"
                                    value="{{ $registration->bus_state }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">LGA</label>
                                <input type="text" name="bus_lga" class="form-control"
                                    value="{{ $registration->bus_lga }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">City/Town/Village</label>
                                <input type="text" name="bus_city" class="form-control"
                                    value="{{ $registration->bus_city }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">House Number</label>
                                <input type="text" name="bus_house_number" class="form-control"
                                    value="{{ $registration->bus_house_number }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Street Name</label>
                                <input type="text" name="bus_street_name" class="form-control"
                                    value="{{ $registration->bus_street_name }}" required>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Description of Business Address</label>
                                <textarea name="bus_description" class="form-control" rows="2" required>{{ $registration->bus_description }}</textarea>
                            </div>
                        </div>

                        {{-- UPLOADS --}}
                        <h5 class="mt-4 mb-2">Upload Supporting Documents <small class="text-muted">(Optional if already
                                uploaded)</small></h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">NIN</label>
                                <input type="file" name="nin" accept=".png,.jpg,.jpeg,.pdf" class="form-control">
                                @if ($registration->nin_path)
                                    <small class="text-muted">Current: <a
                                            href="{{ asset('storage/' . $registration->nin_path) }}"
                                            target="_blank">View</a></small>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Signature</label>
                                <input type="file" name="signature" accept=".png,.jpg,.jpeg" class="form-control">
                                @if ($registration->signature_path)
                                    <small class="text-muted">Current: <a
                                            href="{{ asset('storage/' . $registration->signature_path) }}"
                                            target="_blank">View</a></small>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Passport</label>
                                <input type="file" name="passport" accept=".png,.jpg,.jpeg" class="form-control">
                                @if ($registration->passport_path)
                                    <small class="text-muted">Current: <a
                                            href="{{ asset('storage/' . $registration->passport_path) }}"
                                            target="_blank">View</a></small>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary px-4">Update & Resubmit Registration</button>
                            <a href="{{ route('user.business.create') }}" class="btn btn-light px-4">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection
