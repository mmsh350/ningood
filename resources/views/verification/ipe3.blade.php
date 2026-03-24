@extends('layouts.dashboard')

@section('title', 'IPE Clearance V2')
@push('styles')
    <style>
        .pagination .page-link {
            min-width: 36px;
            text-align: center;
        }

        @media (max-width: 576px) {
            .pagination {
                font-size: 0.75rem;
            }
        }

        .small-card {
            border-radius: 0.5rem;
            font-size: 0.875rem;
        }

        .border:hover {
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
            transform: scale(1.02);
            transition: all 0.2s ease-in-out;
        }
    </style>
@endpush
@section('content')
    <div class="row">

        <div class="mb-3 mt-1">
            <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        </div>
        <div class="col-lg-12 grid-margin d-flex flex-column">
            <div class=" grid-margin stretch-card col-md-12   grid-margin stretch-card ">
                <div class="card">
                    <div class="card-body">
                        <div class="col-12 mb-3">
                            <div class="mb-2">
                                <h6 class="text-center text-uppercase text-muted fw-semibold mb-3"
                                    style="font-size: 0.85rem;">
                                    Total IPE Clearance V2 Requests
                                </h6>

                                <div class="row g-2 justify-content-center">
                                    @php
                                        $metrics = [
                                            [
                                                'label' => 'All',
                                                'value' => $totalAll,
                                                'bg' => '#f8f9fa',
                                                'text' => 'text-dark',
                                                'border' => 'border',
                                            ],
                                            [
                                                'label' => 'Pending',
                                                'value' => $totalPending,
                                                'bg' => '#fff3cd',
                                                'text' => 'text-dark',
                                                'border' => 'border-warning',
                                            ],
                                            [
                                                'label' => 'Failed',
                                                'value' => $totalFailed,
                                                'bg' => '#f8d7da',
                                                'text' => 'text-danger',
                                                'border' => 'border-danger',
                                            ],
                                            [
                                                'label' => 'Successful',
                                                'value' => $totalSuccessful,
                                                'bg' => '#d1e7dd',
                                                'text' => 'text-success',
                                                'border' => 'border-success',
                                            ],
                                            [
                                                'label' => 'Processing',
                                                'value' => $totalProcessing,
                                                'bg' => '#cfe2ff',
                                                'text' => 'text-primary',
                                                'border' => 'border-primary',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($metrics as $metric)
                                        <div class="col-6 col-sm-3 col-lg-2">
                                            <div class="border rounded-3 text-center py-2 px-1 shadow-sm {{ $metric['text'] }}"
                                                style="background: {{ $metric['bg'] }}; font-size: 0.85rem;">
                                                <div class="small fw-light mb-1">{{ $metric['label'] }}</div>
                                                <div class="fw-bold" style="font-size: 1.1rem;">{{ $metric['value'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">IPE CLEARANCE V2 (Automated)</h4>
                        <p class="card-description">Send your IPE Clearance V2 request to get your old tracking number</p>
                        <div class="alert alert-warning shadow-sm text-dark">
                            Refunds for failed IPE V2 requests are processed automatically.
                        </div>
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
                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <form name="ipe-form" id="ipe-form" method="POST"
                                            action="{{ route('user.ipe.request.v3') }}">
                                            @csrf
                                            <div class="mb-3 row">

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-12 mb-0">
                                                            <p class="form-label">Tracking Number</p>
                                                            <input type="text" id="trackingId" name="trackingId"
                                                                maxlength="15" class="form-control"
                                                                placeholder="Enter Tracking ID" required />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-1 mb-2">

                                                <p class="fw-bold mt-2 text-danger size-12"> Service Fee:
                                                    &#x20A6;{{ number_format($ServiceFee->amount ?? 0, 2) }}</p>

                                            </div>
                                            <button type="submit" id="submit" name="submit"
                                                class="btn btn-primary mb-5"><i class="las la-share"></i> Submit
                                                Request
                                            </button>
                                        </form>
                                    </div>
                                    <div class="col-md-12 col-12">
                                        <form method="GET" action="{{ route('user.ipe.v3') }}"
                                            class="row g-2 mb-3 mt-2 align-items-end">

                                            <div class="row g-2">
                                                <div class="col-md-3">
                                                    <label for="search"
                                                        class="form-label d-block d-md-none">Search</label>
                                                    <input type="text" id="search" name="search" class="form-control"
                                                        value="{{ request('search') }}" placeholder="Search Here ...">
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="date_from" class="form-label d-block d-md-none">Start
                                                        Date</label>
                                                    <input type="date" id="date_from" name="date_from"
                                                        class="form-control" value="{{ request('date_from') }}"
                                                        placeholder="Start Date">
                                                </div>

                                                <div class="col-md-3">
                                                    <label for="date_to" class="form-label d-block d-md-none">End
                                                        Date</label>
                                                    <input type="date" id="date_to" name="date_to" class="form-control"
                                                        value="{{ request('date_to') }}" placeholder="End Date">
                                                </div>

                                                <div class="col-md-3">
                                                    <span class="form-label d-block d-md-none">&nbsp;</span>
                                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                        @if (!$ipes->isEmpty())
                                            @php
                                                $currentPage = $ipes->currentPage();
                                                $perPage = $ipes->perPage();
                                            @endphp

                                            <div>
                                                <!-- Desktop Table View -->
                                                <div class="table-responsive">
                                                    <table class="table text-nowrap"
                                                        style="background:#fafafc !important">
                                                        <thead>
                                                            <tr class="table-primary">
                                                                <th width="5%" scope="col">ID</th>
                                                                <th scope="col">Tracking ID.</th>
                                                                <th scope="col">Status</th>
                                                                <th scope="col">Date</th>
                                                                <th scope="col" class="text-center">Response</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($ipes as $index => $data)
                                                                <tr>
                                                                    <th scope="row">
                                                                        {{ ($currentPage - 1) * $perPage + $index + 1 }}
                                                                    </th>
                                                                    <td>{{ $data->trackingId }}</td>
                                                                    <td>
                                                                        @if ($data->status == 'pending')
                                                                            <span class="badge bg-warning">Pending</span>
                                                                        @elseif($data->status == 'successful')
                                                                            <span
                                                                                class="badge bg-success">Successful</span>
                                                                        @elseif($data->status == 'failed')
                                                                            <span class="badge bg-danger">Failed</span>
                                                                        @elseif($data->status == 'processing')
                                                                            <span class="badge bg-info">Processing</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $data->created_at }}</td>

                                                                    <td class="text-center">
                                                                        @if (in_array($data->status, ['pending', 'processing']))
                                                                            waiting for response ...
                                                                        @else
                                                                            {!! $data->reply !!}
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- Pagination -->
                                            <div class="d-flex justify-content-center mt-4">
                                                {{ $ipes->links('vendor.pagination.bootstrap-4') }}
                                            </div>
                                        @else
                                            <div class="text-center py-5">
                                                <img width="65%" src="{{ asset('assets/images/no-transaction.gif') }}"
                                                    alt="No transactions">
                                                <p class="text-center fw-semibold fs-5 mt-3">No Requests Available!</p>
                                            </div>
                                        @endif
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('ipe-form');
            const submitButton = document.getElementById('submit');

            if (form) {
                form.addEventListener('submit', function() {
                    submitButton.disabled = true;
                    submitButton.innerText = 'Please wait while we process your request...';
                });
            }
        });
    </script>
@endpush
