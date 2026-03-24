@extends('layouts.dashboard')

@section('title', 'Admin - CAC Company Registrations')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <style>
        .form-check .form-check-input {
            margin-left: 0;
        }

        .avatar-rounded {
            border-radius: 50%;
        }

        .bg-primary-transparent {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .bg-success-transparent {
            background-color: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .bg-warning-transparent {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .bg-danger-transparent {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .bg-info-transparent {
            background-color: rgba(13, 202, 240, 0.1);
            color: #0dcaf0;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h5 class="card-title">Company Registration List(CAC)</h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {!! session('success') !!}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            {{-- Stats Cards --}}
                            <div class="row mb-3">
                                <div class="col-xxl-3 col-lg-3 col-md-3">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                                        <i class="las la-tasks fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">All Request</p>
                                                            <h4 class="fw-semibold mt-1">
                                                                {{ $pending + $processing + $completed + $failed + $queried }}
                                                            </h4>
                                                        </div>
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
                                                    <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                                        <i class="las la-check-double fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Completed</p>
                                                            <h4 class="fw-semibold mt-1">{{ $completed }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-lg-3 col-md-3">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                                        <i class="las la-list-alt fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Pending</p>
                                                            <h4 class="fw-semibold mt-1">{{ $pending }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-lg-3 col-md-3 mt-2 mt-md-0">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded bg-danger-transparent">
                                                        <i class="las la-list-alt fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Failed</p>
                                                            <h4 class="fw-semibold mt-1">{{ $failed }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xxl-2 col-lg-3 col-md-3 mt-2 mt-md-0">
                                    <div class="card custom-card overflow-hidden">
                                        <div class="card-body">
                                            <div class="d-flex align-items-top justify-content-between">
                                                <div>
                                                    <span class="avatar avatar-md avatar-rounded bg-info-transparent">
                                                        <i class="las la-list-alt fs-4"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill ms-3">
                                                    <div
                                                        class="d-flex align-items-center justify-content-between flex-wrap">
                                                        <div>
                                                            <p class="text-muted mb-0">Queried</p>
                                                            <h4 class="fw-semibold mt-1">{{ $queried }}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Filter Form --}}
                            <form method="GET" action="{{ route('admin.company.index') }}"
                                class="row g-2 mb-3 align-items-end">
                                <div class="col-md-9">
                                    <input type="text" name="search" class="form-control"
                                        value="{{ request('search') }}"
                                        placeholder="Search by Reference, Director Name, or Business Name...">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                                </div>
                            </form>

                            {{-- Table --}}
                            <div class="table-responsive">
                                <table class="table text-nowrap" style="background:#fafafc !important">
                                    <thead>
                                        <tr>
                                            <th>SN</th>
                                            <th>User</th>
                                            <th>Reference Number</th>
                                            <th>Business Name</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($registrationList as $reg)
                                            <tr class="align-middle">
                                                <td>{{ $loop->iteration + ($registrationList->currentPage() - 1) * $registrationList->perPage() }}
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary-transparent me-2 rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold"
                                                            style="width: 32px; height: 32px; font-size: 12px;">
                                                            {{ substr($reg->user->name ?? '?', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <p class="mb-0 fw-bold small text-dark">
                                                                {{ $reg->user->name ?? 'Unknown' }}</p>
                                                            <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                                                                {{ $reg->user->email ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><span class="fw-semibold text-dark">{{ $reg->refno }}</span></td>
                                                <td><span class="text-dark">{{ $reg->business_name_1 }}</span></td>
                                                <td>{{ $reg->created_at->format('d M, Y H:i') }}</td>
                                                <td>
                                                    @if ($reg->status == 'pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                    @elseif($reg->status == 'processing')
                                                        <span class="badge bg-primary">Processing</span>
                                                    @elseif($reg->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($reg->status == 'query')
                                                        <span class="badge bg-info">Queried</span>
                                                    @else
                                                        <span class="badge bg-danger">Failed</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.company.show', $reg->id) }}"
                                                        class="btn btn-primary btn-sm">
                                                        <i class="las la-eye me-1"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">No registration
                                                    requests found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-center mt-3">
                                {{ $registrationList->appends(request()->input())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
