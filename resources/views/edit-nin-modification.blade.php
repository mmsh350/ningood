@extends('layouts.dashboard')

@section('title', 'Edit NIN Modification Request')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title text-white mb-0">Edit NIN Modification Request - {{ $modRequest->refno }}</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif



                    <div class="card border-0 shadow-sm mb-4" style="background: #fbf9ff; border-radius: 10px;">
                        <div class="card-header border-0 bg-transparent pt-3 pb-0">
                            <h6 class="fw-bold mb-0" style="color: #2563eb;">

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

                    <form action="{{ route('user.nin-modification.update', $modRequest->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nin" class="form-label fw-semibold">NIN Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="nin" name="nin" maxlength="11" class="form-control"
                                    value="{{ $modRequest->nin_number }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label fw-semibold">First Name</label>
                                <input type="text" id="firstname" name="firstname" class="form-control"
                                    value="{{ $modRequest->first_name }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="middlename" class="form-label fw-semibold">Middle Name</label>
                                <input type="text" id="middlename" name="middlename" class="form-control"
                                    value="{{ $modRequest->middle_name }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="surname" class="form-label fw-semibold">Surname</label>
                                <input type="text" id="surname" name="surname" class="form-control"
                                    value="{{ $modRequest->surname }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" id="dob" name="dob" class="form-control"
                                    value="{{ $modRequest->dob }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    value="{{ $modRequest->phone_number }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label fw-semibold">State</label>
                                <input type="text" id="state" name="state" class="form-control"
                                    value="{{ $modRequest->state }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lga" class="form-label fw-semibold">LGA</label>
                                <input type="text" id="lga" name="lga" class="form-control"
                                    value="{{ $modRequest->lga }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label fw-semibold">Residential Address</label>
                            <textarea id="address" name="address" class="form-control" rows="2">{{ $modRequest->address }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_address" class="form-label fw-semibold">Full Address</label>
                                <textarea id="full_address" name="full_address" class="form-control" rows="2">{{ $modRequest->full_address }}</textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="origin_address" class="form-label fw-semibold">Place of Origin Address</label>
                                <textarea id="origin_address" name="origin_address" class="form-control" rows="2">{{ $modRequest->origin_address }}</textarea>
                            </div>
                        </div>

                        @if ($modRequest->education_qualification || $modRequest->marital_status || $modRequest->father_full_name)
                            <hr>
                            <h5 class="fw-bold mb-3">Attestation Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="education_qualification" class="form-label fw-semibold">Education
                                        Qualification</label>
                                    <input type="text" id="education_qualification" name="education_qualification"
                                        class="form-control" value="{{ $modRequest->education_qualification }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="marital_status" class="form-label fw-semibold">Marital Status</label>
                                    <input type="text" id="marital_status" name="marital_status" class="form-control"
                                        value="{{ $modRequest->marital_status }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <h6 class="fw-bold">Father's Details</h6>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="father_full_name" class="form-label fw-semibold">Father's Full
                                        Name</label>
                                    <input type="text" id="father_full_name" name="father_full_name"
                                        class="form-control" value="{{ $modRequest->father_full_name }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="father_state_of_origin" class="form-label fw-semibold">Father's State of
                                        Origin</label>
                                    <input type="text" id="father_state_of_origin" name="father_state_of_origin"
                                        class="form-control" value="{{ $modRequest->father_state_of_origin }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="father_lga_of_origin" class="form-label fw-semibold">Father's LGA of
                                        Origin</label>
                                    <input type="text" id="father_lga_of_origin" name="father_lga_of_origin"
                                        class="form-control" value="{{ $modRequest->father_lga_of_origin }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <h6 class="fw-bold">Mother's Details</h6>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="mother_full_name" class="form-label fw-semibold">Mother's Full
                                        Name</label>
                                    <input type="text" id="mother_full_name" name="mother_full_name"
                                        class="form-control" value="{{ $modRequest->mother_full_name }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="mother_state_of_origin" class="form-label fw-semibold">Mother's State of
                                        Origin</label>
                                    <input type="text" id="mother_state_of_origin" name="mother_state_of_origin"
                                        class="form-control" value="{{ $modRequest->mother_state_of_origin }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="mother_lga_of_origin" class="form-label fw-semibold">Mother's LGA of
                                        Origin</label>
                                    <input type="text" id="mother_lga_of_origin" name="mother_lga_of_origin"
                                        class="form-control" value="{{ $modRequest->mother_lga_of_origin }}">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="mother_maiden_name" class="form-label fw-semibold">Mother's Maiden
                                        Name</label>
                                    <input type="text" id="mother_maiden_name" name="mother_maiden_name"
                                        class="form-control" value="{{ $modRequest->mother_maiden_name }}">
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="photo" class="form-label fw-semibold">Upload Photo</label>
                                <input type="file" id="photo" name="photo" class="form-control"
                                    accept="image/*">
                                <small class="text-muted">Current photo: <a
                                        href="{{ asset('storage/' . $modRequest->photo) }}"
                                        target="_blank">View</a></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="affidavit" class="form-label fw-semibold">Upload Affidavit (Optional)</label>
                                <input type="file" id="affidavit" name="affidavit" class="form-control"
                                    accept="image/*,application/pdf">
                                @if ($modRequest->affidavit)
                                    <small class="text-muted">Current affidavit: <a
                                            href="{{ asset('storage/' . $modRequest->affidavit) }}"
                                            target="_blank">View</a></small>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn text-light w-100 py-2 fw-bold" style="background:#2563eb">
                                Update and Resubmit Request
                            </button>
                            <a href="{{ route('user.nin.mod') }}" class="btn btn-light w-100 mt-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
