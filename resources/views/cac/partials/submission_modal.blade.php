<div class="modal fade" id="viewModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Registration Details - {{ $submission->refno }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Status and Basic Info -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <p><strong>Status:</strong></p>
                        @php
                            $badgeClass = match ($submission->status) {
                                'pending' => 'badge bg-warning',
                                'processing' => 'badge bg-info',
                                'completed' => 'badge bg-success',
                                'query' => 'badge bg-danger',
                                default => 'badge bg-secondary',
                            };
                        @endphp
                        <span class="{{ $badgeClass }} border-0">{{ ucfirst($submission->status) }}</span>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Submitted Date:</strong></p>
                        <p class="text-primary fw-bold">{{ $submission->created_at->format('d M Y h:i A') }}</p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Reference Number:</strong></p>
                        <p class="text-primary fw-bold">{{ $submission->refno }}</p>
                    </div>
                </div>

                <!-- Admin Feedback -->
                @if ($submission->admin_comment)
                    <div class="alert alert-info border-0 shadow-sm p-3 mb-4"
                        style="background-color: #f0f7ff; border-radius: 10px;">
                        <div class="d-flex align-items-center mb-2">
                            <i class="las la-exclamation-circle text-info fs-5 me-2"></i>
                            <span class="fw-bold text-info">Administrator Feedback</span>
                        </div>
                        <div class="bg-white p-3 rounded border border-info-subtle small text-dark shadow-none"
                            style="line-height: 1.6;">
                            {!! $submission->admin_comment !!}
                        </div>
                    </div>
                @endif

                <div class="border-bottom mb-4"></div>

                <!-- Director & Business Information -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="fw-bold text-dark border-bottom pb-1">Director Information</p>
                        <ul class="list-unstyled small">
                            <li><strong>Full Name:</strong> <span
                                    class="text-primary fw-bold">{{ $submission->director_surname }}
                                    {{ $submission->director_firstname }} {{ $submission->director_othername }}</span>
                            </li>
                            <li class="mt-1"><strong>Gender:</strong> {{ $submission->director_gender }}</li>
                            <li class="mt-1"><strong>Date of Birth:</strong>
                                {{ $submission->director_dob->format('M d, Y') }}</li>
                            <li class="mt-1"><strong>NIN:</strong> <span
                                    class="text-primary fw-bold">{{ $submission->director_nin }}</span></li>
                            <li class="mt-1"><strong>Phone:</strong> {{ $submission->director_phone }}</li>
                            <li class="mt-1"><strong>Email:</strong> {{ $submission->director_email }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-bold text-dark border-bottom pb-1">Business Information</p>
                        <ul class="list-unstyled small">
                            <li><strong>Proposed Name 1:</strong> <span
                                    class="text-primary fw-bold">{{ $submission->business_name_1 }}</span></li>
                            <li class="mt-1"><strong>Proposed Name 2:</strong> <span
                                    class="text-primary fw-bold">{{ $submission->business_name_2 }}</span></li>
                            <li class="mt-1"><strong>Nature of Business:</strong>
                                {{ $submission->nature_of_business }}</li>
                            <li class="mt-1"><strong>Business Email:</strong> {{ $submission->business_email }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Addresses -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="fw-bold text-dark border-bottom pb-1">Residential Address</p>
                        <p class="small text-muted mb-1">
                            {{ $submission->res_house_number }}, {{ $submission->res_street_name }}<br>
                            {{ $submission->res_city }}, {{ $submission->res_lga }}<br>
                            {{ $submission->res_state }} State.
                        </p>
                        <p class="x-small text-muted italic"><i>{{ $submission->res_description }}</i></p>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-bold text-dark border-bottom pb-1">Business Address</p>
                        <p class="small text-muted mb-1">
                            {{ $submission->bus_house_number }}, {{ $submission->bus_street_name }}<br>
                            {{ $submission->bus_city }}, {{ $submission->bus_lga }}<br>
                            {{ $submission->bus_state }} State.
                        </p>
                        <p class="x-small text-muted italic"><i>{{ $submission->bus_description }}</i></p>
                    </div>
                </div>

                <!-- Witness & Shareholder -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="fw-bold text-dark border-bottom pb-1">Witness Details</p>
                        <ul class="list-unstyled small">
                            <li><strong>Name:</strong> {{ $submission->witness_surname }}
                                {{ $submission->witness_firstname }}</li>
                            <li class="mt-1"><strong>Phone:</strong> {{ $submission->witness_phone }}</li>
                            <li class="mt-1"><strong>NIN:</strong> {{ $submission->witness_nin }}</li>
                            <li class="mt-1"><strong>Address:</strong> {{ $submission->witness_address }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-bold text-dark border-bottom pb-1">Shareholder Details</p>
                        <ul class="list-unstyled small">
                            <li><strong>Name:</strong> {{ $submission->shareholder_surname }}
                                {{ $submission->shareholder_firstname }}</li>
                            <li class="mt-1"><strong>Phone:</strong> {{ $submission->shareholder_phone }}</li>
                            <li class="mt-1"><strong>NIN:</strong> {{ $submission->shareholder_nin }}</li>
                            <li class="mt-1"><strong>Date of Birth:</strong>
                                {{ $submission->shareholder_dob ? $submission->shareholder_dob->format('M d, Y') : 'N/A' }}
                            </li>
                            <li class="mt-1"><strong>Gender/Nationality:</strong>
                                {{ $submission->shareholder_gender }} | {{ $submission->shareholder_nationality }}
                            </li>
                        </ul>
                    </div>
                </div>

                <hr>

                <!-- Documents -->
                <div class="row mb-3">
                    <div class="col-12">
                        <p class="fw-bold text-dark mb-2">Submitted Documents</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ asset('storage/' . $submission->director_signature_path) }}" target="_blank"
                                class="btn btn-sm btn-outline-dark">
                                <i class="las la-signature me-1"></i> Director Signature
                            </a>
                            <a href="{{ asset('storage/' . $submission->witness_signature_path) }}" target="_blank"
                                class="btn btn-sm btn-outline-dark">
                                <i class="las la-signature me-1"></i> Witness Signature
                            </a>
                            <a href="{{ asset('storage/' . $submission->shareholder_signature_path) }}" target="_blank"
                                class="btn btn-sm btn-outline-dark">
                                <i class="las la-signature me-1"></i> Shareholder Signature
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Response Documents -->
                @if ($submission->response_documents)
                    <div class="row mb-3">
                        <div class="col-12">
                            <p class="fw-bold text-dark mb-2">Official Documents</p>
                            <div class="list-group list-group-flush border rounded">
                                @foreach ($submission->response_documents as $doc)
                                    <a href="{{ asset('storage/' . $doc) }}" target="_blank"
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                                        <span class="small"><i class="las la-file-pdf text-danger me-2"></i>
                                            {{ basename($doc) }}</span>
                                        <span class="badge bg-primary rounded-pill small">Download</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                @if ($submission->status == 'query')
                    <a href="{{ route('user.company.edit', $submission->id) }}" class="btn btn-primary px-4">
                        <i class="las la-edit me-1"></i> Edit & Resubmit
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
