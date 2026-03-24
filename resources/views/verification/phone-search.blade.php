@extends('layouts.dashboard')

@section('title', 'BVN Phone Search')

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">BVN Phone Search</h4>

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="new-tab" data-bs-toggle="tab" href="#new-1" role="tab"
                                aria-controls="new-1" aria-selected="true">New</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="new-1" role="tabpanel" aria-labelledby="new-tab">

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

                            <!-- Compact Search Form -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body p-3">
                                            <form name="validation-form" id="validation-form" method="POST"
                                                action="{{ route('user.bvn-phone-request') }}"
                                                enctype="multipart/form-data">
                                                @csrf

                                                <!-- Search Row -->
                                                <div class="row g-3 align-items-end">
                                                    <small class="text-danger mb-2">
                                                        <div class="alert alert-primary d-flex align-items-start"
                                                            role="alert">
                                                            <div>
                                                                <strong class="text-danger">⏱️ Results ready within 6
                                                                    working hours
                                                                    (during business hours).</strong><br>
                                                                Requests submitted after working hours will be
                                                                treated the next morning.
                                                            </div>
                                                        </div>

                                                    </small>
                                                    <div class="col-md-4">
                                                        <label class="form-label small text-muted mb-1">Phone Number</label>
                                                        <input type="text" id="phone_number" name="phone_number"
                                                            maxlength="11" class="form-control" required
                                                            placeholder="Enter phone number" />
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="d-flex flex-column">
                                                            <div class="d-flex align-items-center">
                                                                <span class="badge bg-primary me-2">Fee:
                                                                    ₦{{ number_format($ServiceFee->amount, 2) }}</span>
                                                                <button type="submit" id="submit"
                                                                    style="background:#2563eb" name="submit"
                                                                    class="btn btn-sm text-light">
                                                                    <i class="las la-search"></i> Submit Request
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- History Section -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h5 class="card-title mb-0">Request History</h5>
                                                <button class="btn btn-outline-info btn-sm" type="button"
                                                    data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                                    <i class="las la-filter"></i> Filters
                                                </button>
                                            </div>

                                            <!-- Collapsible Filters -->
                                            <div class="collapse mb-3" id="filterCollapse">
                                                <div class="card card-body">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control form-control-sm"
                                                                id="filter_phone" name="filter_phone"
                                                                value="{{ request('filter_phone') }}"
                                                                placeholder="Phone number">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" class="form-control form-control-sm"
                                                                id="filter_ref" name="filter_ref"
                                                                value="{{ request('filter_ref') }}"
                                                                placeholder="Reference number">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <select class="form-control form-control-sm" id="filter_status"
                                                                name="filter_status">
                                                                <option value="">All Status</option>
                                                                <option value="pending"
                                                                    {{ request('filter_status') == 'pending' ? 'selected' : '' }}>
                                                                    Pending</option>
                                                                <option value="resolved"
                                                                    {{ request('filter_status') == 'resolved' ? 'selected' : '' }}>
                                                                    Resolved</option>
                                                                <option value="rejected"
                                                                    {{ request('filter_status') == 'rejected' ? 'selected' : '' }}>
                                                                    Rejected</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <input type="date" class="form-control form-control-sm"
                                                                id="filter_date" name="filter_date"
                                                                value="{{ request('filter_date') }}">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="d-flex gap-1">
                                                                <button type="button" id="applyFilters"
                                                                    class="btn btn-primary btn-sm flex-fill">
                                                                    <i class="las la-check"></i> Apply
                                                                </button>
                                                                <button type="button" id="resetFilters"
                                                                    class="btn btn-outline-primary btn-sm">
                                                                    <i class="las la-redo-alt"></i> Reset
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if (!$bvns->isEmpty())
                                                @php
                                                    $currentPage = $bvns->currentPage();
                                                    $perPage = $bvns->perPage();
                                                    $serialNumber = ($currentPage - 1) * $perPage + 1;
                                                @endphp
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-hover">
                                                        <thead>
                                                            <tr class="table-light">
                                                                <th width="5%">#</th>
                                                                <th>Reference No.</th>
                                                                <th>Phone Number</th>
                                                                <th>BVN No</th>
                                                                <th class="text-center">Status</th>
                                                                <th>Date</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($bvns as $data)
                                                                <tr>
                                                                    <td>{{ $serialNumber++ }}</td>
                                                                    <td>
                                                                        <span
                                                                            class="font-monospace text-uppercase">{{ $data->refno }}</span>
                                                                    </td>
                                                                    <td>{{ $data->phone_number }}</td>
                                                                    <td>{{ $data->reason }}</td>
                                                                    <td class="text-center">
                                                                        @if ($data->status == 'resolved')
                                                                            <span
                                                                                class="badge bg-success">{{ Str::upper($data->status) }}</span>
                                                                        @elseif($data->status == 'rejected')
                                                                            <span
                                                                                class="badge bg-danger">{{ Str::upper($data->status) }}</span>
                                                                        @elseif($data->status == 'pending')
                                                                            <span
                                                                                class="badge bg-warning">{{ Str::upper($data->status) }}</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-primary">{{ Str::upper($data->status) }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <small
                                                                            class="text-muted">{{ $data->created_at->format('M d, Y') }}</small>
                                                                    </td>
                                                                    <td class="">
                                                                        <button type="button" data-bs-toggle="modal"
                                                                            data-reason="{{ $data->reason ?? 'No reason provided' }}"
                                                                            data-bs-target="#reason"
                                                                            class="btn btn-outline-info btn-sm">
                                                                            <i class="fa fa-eye"></i> View
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>

                                                    <!-- Pagination -->
                                                    <div class="d-flex justify-content-center mt-3">
                                                        {{ $bvns->appends(request()->query())->links('vendor.pagination.bootstrap-4') }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <img width="40%"
                                                        src="{{ asset('assets/images/no-transaction.gif') }}"
                                                        alt="No transactions" class="mb-3">
                                                    <p class="text-muted">No search history available</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reason Modal -->
                            <div class="modal fade" id="reason" tabindex="-1" aria-labelledby="reasonLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h6 class="modal-title" id="reasonLabel">Query Details</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p id="reasonMessage" class="mb-0">No details available.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
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
        $(document).ready(function() {
            // Reason Modal
            $('#reason').on('shown.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var reason = button.data('reason');
                $("#reasonMessage").html(reason || 'No details available.');
            });

            // Form submission loader
            $('#validation-form').on('submit', function() {
                const submitButton = $('#submit');
                submitButton.prop('disabled', true);
                submitButton.html('<i class="las la-spinner la-spin"></i> Searching...');
            });

            // Filter functionality
            $('#applyFilters').click(applyFilters);
            $('#resetFilters').click(resetFilters);

            // Enter key support for filters
            $('#filter_phone, #filter_ref, #filter_status, #filter_date').keypress(function(e) {
                if (e.which == 13) applyFilters();
            });

            function applyFilters() {
                const params = new URLSearchParams();
                const filters = ['filter_phone', 'filter_ref', 'filter_status', 'filter_date'];

                filters.forEach(filter => {
                    const value = $(`#${filter}`).val();
                    if (value) params.append(filter, value);
                });

                window.location.href = '{{ url()->current() }}?' + params.toString();
            }

            function resetFilters() {
                window.location.href = '{{ url()->current() }}';
            }
        });
    </script>
@endpush
