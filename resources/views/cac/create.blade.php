@extends('layouts.dashboard')

@section('title', 'CAC Company Registration')

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

            <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">

                {{-- CAC Logo + Service Fee --}}
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center my-3 px-4">
                    <div class="text-center text-sm-start">
                        <h4 class="mb-1 fw-bold">CAC Company Registration <span class="text-muted d-block d-sm-inline"
                                style="font-size: 0.9rem; font-weight: normal;">(Individual only)</span></h4>
                    </div>

                    {{-- Logo placeholder --}}
                    <div class="mt-3 mt-sm-0">
                        <img src="{{ asset('assets/images/img/icon/cac.png') }}" alt="CAC Logo" height="60">
                    </div>
                </div>
                <div class="card-header bg-white border-0 py-3">
                    <ul class="nav nav-pills custom-pills" id="cacTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4" id="form-tab" data-bs-toggle="tab"
                                data-bs-target="#formTab" type="button" role="tab">
                                <i class="las la-file-invoice me-1"></i> New Registration
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4" id="history-tab" data-bs-toggle="tab"
                                data-bs-target="#historyTab" type="button" role="tab">
                                <i class="las la-history me-1"></i> My Submissions
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4 pt-0">
                    <div class="tab-content" id="cacTabsContent">
                        {{-- FORM TAB --}}
                        <div class="tab-pane fade show active" id="formTab" role="tabpanel">
                            <div class="alert alert-info border-0 shadow-sm mb-4" style=" ">
                                <div class="d-flex align-items-center">
                                    <i class="las la-info-circle fs-3 me-3"></i>
                                    <div>
                                        <h5 class="mb-1">Service Fee: ₦{{ number_format($ServiceFee, 2) }}</h5>
                                        <h6 class="fw-bold mb-1 mt-2">Important Disclaimer</h6>
                                        <p class="mb-0 small">This Company Registration service is intended for
                                            **Individual** registration only and does not support Partnership structures at
                                            this time.</p>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('user.company.store') }}" enctype="multipart/form-data"
                                id="cacRegForm">
                                @csrf

                                {{-- 1. DIRECTOR INFORMATION --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">1</div>
                                        <h5 class="mb-0 fw-bold text-dark">Director Information</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Surname <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="director_surname" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">First Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="director_firstname" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Other Name</label>
                                            <input type="text" name="director_othername" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Date of Birth <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="director_dob" class="form-control" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Gender <span
                                                    class="text-danger">*</span></label>
                                            <select name="director_gender" class="form-select text-dark"
                                                style="color: #6c757d !important;" required>
                                                <option value="">Select Gender</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="director_email" class="form-control" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Phone <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="director_phone" maxlength="11"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">NIN (National Identity Number) <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="director_nin" maxlength="11"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. RESIDENTIAL ADDRESS --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">2</div>
                                        <h5 class="mb-0 fw-bold text-dark">Residential Address</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">State <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="res_state" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">LGA <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="res_lga" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">City/Town/Village <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="res_city" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">House Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="res_house_number" class="form-control" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Street Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="res_street_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description of Address <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="res_description" class="form-control" rows="2" required
                                                placeholder="Describe the location e.g. blue gate opposite the bank"></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. BUSINESS ADDRESS --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">3</div>
                                        <h5 class="mb-0 fw-bold text-dark">Business Address</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">State <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bus_state" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">LGA <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bus_lga" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">City/Town/Village <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bus_city" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">House Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bus_house_number" class="form-control" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Street Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="bus_street_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Description of Business Address <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="bus_description" class="form-control" rows="2" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- 4. BUSINESS INFORMATION --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">4</div>
                                        <h5 class="mb-0 fw-bold text-dark">Business Details</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Nature of Business <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="nature_of_business" class="form-control" rows="2" required
                                                placeholder="Briefly explain what the company does"></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Name Option 1 <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="business_name_1" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Business Name Option 2 <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="business_name_2" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Functional Business Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="business_email" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                {{-- 5. WITNESS INFORMATION --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">5</div>
                                        <h5 class="mb-0 fw-bold text-dark">Witness Information</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Surname <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="witness_surname" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">First Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="witness_firstname" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Other Name</label>
                                            <input type="text" name="witness_othername" class="form-control">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="witness_phone" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Email <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="witness_email" class="form-control" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">NIN Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="witness_nin" maxlength="11" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Witness Home Address (State, LGA, House
                                                No & Street) <span class="text-danger">*</span></label>
                                            <textarea name="witness_address" class="form-control" rows="2" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- 6. SHAREHOLDER INFORMATION --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">6</div>
                                        <h5 class="mb-0 fw-bold text-dark">Shareholder Information</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Surname <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shareholder_surname" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">First Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shareholder_firstname" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Other Name</label>
                                            <input type="text" name="shareholder_othername" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Date of Birth <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" name="shareholder_dob" class="form-control" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Gender <span
                                                    class="text-danger">*</span></label>
                                            <select name="shareholder_gender" class="form-select text-dark"
                                                style="color: #6c757d !important;" required>
                                                <option value="">Select Gender</option>
                                                <option>Male</option>
                                                <option>Female</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Nationality <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shareholder_nationality" maxlength="11"
                                                class="form-control" required value="Nigerian">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Phone Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shareholder_phone" maxlength="11"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Email address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="shareholder_email" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">NIN Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="shareholder_nin" maxlength="11"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label fw-semibold">Shareholder Home Address (State, LGA,
                                                House No & Street) <span class="text-danger">*</span></label>
                                            <textarea name="shareholder_address" class="form-control" rows="2" required></textarea>
                                        </div>
                                    </div>
                                </div>

                                {{-- 7. UPLOADS --}}
                                <div class="form-section mb-5">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="section-number me-3">7</div>
                                        <h5 class="mb-0 fw-bold text-dark">Upload Documents</h5>
                                    </div>
                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="upload-wrapper p-3 border rounded text-center">
                                                <label class="form-label fw-bold d-block mb-3">Signature of
                                                    Director</label>
                                                <input type="file" name="director_signature" class="form-control"
                                                    required accept="image/*">
                                                <small class="text-muted mt-2 d-block">JPG or PNG only</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="upload-wrapper p-3 border rounded text-center">
                                                <label class="form-label fw-bold d-block mb-3">Signature of Witness</label>
                                                <input type="file" name="witness_signature" class="form-control"
                                                    required accept="image/*">
                                                <small class="text-muted mt-2 d-block">JPG or PNG only</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="upload-wrapper p-3 border rounded text-center">
                                                <label class="form-label fw-bold d-block mb-3">Signature of
                                                    Shareholder</label>
                                                <input type="file" name="shareholder_signature" class="form-control"
                                                    required accept="image/*">
                                                <small class="text-muted mt-2 d-block">JPG or PNG only</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-5">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm fw-bold"
                                        style="border-radius: 30px;">
                                        Submit Registration
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="historyTab" role="tabpanel">
                            <h5 class="mt-4 mb-3 fw-bold">Your Submission History</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Business Name</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($submissions as $sub)
                                            <tr>
                                                <td>{{ $sub->created_at->format('d M, Y') }}</td>
                                                <td><span class="fw-bold">{{ $sub->refno }}</span></td>
                                                <td>{{ $sub->business_name_1 }}</td>
                                                <td>
                                                    @if ($sub->status == 'pending')
                                                        <span class="badge bg-warning text-dark">Pending</span>
                                                    @elseif($sub->status == 'processing')
                                                        <span class="badge bg-primary">Processing</span>
                                                    @elseif($sub->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($sub->status == 'query')
                                                        <span class="badge bg-info">Queried</span>
                                                    @else
                                                        <span class="badge bg-danger">Failed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                            data-bs-target="#viewModal{{ $sub->id }}">
                                                            View
                                                        </button>
                                                        @if ($sub->status == 'query')
                                                            <a href="{{ route('user.company.edit', $sub->id) }}"
                                                                class="btn btn-sm btn-info text-white">
                                                                Edit
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- MODAL --}}
                                            @include('cac.partials.submission_modal', [
                                                'submission' => $sub,
                                            ])

                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    You haven't submitted any company registrations yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                {{ $submissions->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .custom-pills .nav-link {
                color: #64748b;
                border-bottom: 3px solid transparent;
                border-radius: 0;
                background: none !important;
            }

            .custom-pills .nav-link.active {
                color: #482666 !important;
                border-bottom: 3px solid #482666;
            }

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

            .bg-warning-transparent {
                background: #fff8eb;
            }

            .bg-primary-transparent {
                background: #eff6ff;
            }

            .bg-danger-transparent {
                background: #fef2f2;
            }

            .bg-success-transparent {
                background: #f0fdf4;
            }

            .bg-dark-transparent {
                background: #f8fafc;
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
