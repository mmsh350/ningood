@extends('layouts.dashboard')

@section('title', 'NIN Services')
@push('styles')
@endpush
@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card ">
                        <div class="card-header">
                            <h5 class="card-title">NIN Services Request</h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {!! session('success') !!}
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

                            <div class="col-xl-12 mb-3">
                                <div class="row">
                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                            <i class="las la-tasks"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">All Request</p>
                                                                <h4 class="fw-semibold mt-1">{{ $total_request }}</h4>
                                                            </div>
                                                            <div id="crm-total-customers"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-success-transparent">
                                                            <i class="las la-check-double"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Sucessful </p>
                                                                <h4 class="fw-semibold mt-1">{{ $resolved }}</h4>
                                                            </div>
                                                            <div id="crm-total-deals"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span
                                                            class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                                            <i class="las la-list-alt"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Pending</p>
                                                                <h4 class="fw-semibold mt-1">{{ $pending }}</h4>
                                                            </div>
                                                            <div id="crm-total-deals"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xxl-3 col-lg-3 col-md-3">
                                        <div class="card custom-card overflow-hidden">
                                            <div class="card-body">
                                                <div class="d-flex align-items-top justify-content-between">
                                                    <div>
                                                        <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                            <i class="las la-list-alt"></i>
                                                        </span>
                                                    </div>
                                                    <div class="flex-fill ms-3">
                                                        <div
                                                            class="d-flex align-items-center justify-content-between flex-wrap">
                                                            <div>
                                                                <p class="text-muted mb-0">Rejected</p>
                                                                <h4 class="fw-semibold mt-1">{{ $rejected }}</h4>
                                                            </div>
                                                            <div id="crm-total-deals"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="GET" action="{{ route('admin.nin.services.list') }}"
                                class="row g-2 mb-3 align-items-end">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <input type="text" name="search" class="form-control"
                                            value="{{ request('search') }}" placeholder="Search Here ...">
                                    </div>

                                    <div class="col-md-3">
                                        <input type="date" name="date_from" class="form-control"
                                            value="{{ request('date_from') }}" placeholder="Start Date">
                                    </div>

                                    <div class="col-md-3">
                                        <input type="date" name="date_to" class="form-control"
                                            value="{{ request('date_to') }}" placeholder="End Date">
                                    </div>

                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table text-nowrap" style="background:#fafafc !important">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>Reference Number</th>
                                            <th>NIN No.</th>
                                            <th>Service Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($nin_services as $service)
                                            <tr>
                                                <td> {{ $loop->iteration }}</td>
                                                <td>{{ $service->refno }}</td>
                                                <td>{{ $service->nin }}</td>
                                                <td>{{ $service->service_type }}</td>
                                                <td>₦ {{ $service->transactions->amount }}</td>
                                                <td>


                                                    @if ($service->status == 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($service->status == 'resolved')
                                                        <span class="badge bg-success">Sucessful </span>
                                                    @elseif($service->status == 'processing')
                                                        <span class="badge bg-primary">Processing</span>
                                                    @else
                                                        <span class="badge bg-danger">Rejected</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($service->user && $service->status !== 'rejected')
                                                        <a href="{{ route('admin.view-request', [$service->id, $request_type]) }}"
                                                            class="btn btn-primary btn-sm text-center">
                                                            <i class="ri-edit-line">View</i>
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center mt-3">
                                {{ $nin_services->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>



            </div>
        </div>
    </div>


@endsection
