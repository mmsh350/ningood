@extends('layouts.dashboard')

@section('title', 'IPE Clearance')
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
                                    Total IPE Clearance Requests
                                </h6>

                                <div class="row g-2 justify-content-center">
                                    @php
                                        $validationStats = [
                                            [
                                                'label' => 'All',
                                                'value' => $total_request,
                                                'bg' => '#f8f9fa',
                                                'text' => 'text-dark',
                                                'border' => 'border',
                                            ],
                                            [
                                                'label' => 'Pending',
                                                'value' => $pending,
                                                'bg' => '#fff3cd',
                                                'text' => 'text-dark',
                                                'border' => 'border-warning',
                                            ],
                                            [
                                                'label' => 'Failed',
                                                'value' => $rejected,
                                                'bg' => '#f8d7da',
                                                'text' => 'text-danger',
                                                'border' => 'border-danger',
                                            ],
                                            [
                                                'label' => 'Successful',
                                                'value' => $resolved,
                                                'bg' => '#d1e7dd',
                                                'text' => 'text-success',
                                                'border' => 'border-success',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($validationStats as $stat)
                                        <div class="col-6 col-sm-3 col-lg-2">
                                            <div class="border rounded-3 text-center py-2 px-1 shadow-sm {{ $stat['text'] }}"
                                                style="background: {{ $stat['bg'] }}; font-size: 0.85rem;">
                                                <div class="small fw-light mb-1">{{ $stat['label'] }}</div>
                                                <div class="fw-bold" style="font-size: 1.1rem;">{{ $stat['value'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title">IPE Clearance</h4>
                        <p class="card-description">Modify the status of the request from this section.</p>

                        <div class="row">

                          <div class="col-xl-12">

                                    <div class="card custom-card ">

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
                                            <div class="col-12  mb-3">
                                                <div class="mb-3 d-flex flex-wrap align-items-center gap-2">
                                                    <a href="{{ route('admin.ipe.download-template') }}"
                                                        class="btn btn-outline-primary">
                                                        <i class="las la-file-excel"></i> Download Excel Data
                                                    </a>

                                                    <form action="{{ route('admin.ipe.upload-excel') }}" method="POST"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        <label for="excel-upload" class="btn btn-outline-success mb-0"
                                                            style="cursor: pointer;">
                                                            <i class="las la-upload"></i> Upload Excel
                                                        </label>
                                                        <input type="file" name="excel_file" id="excel-upload"
                                                            accept=".xlsx,.xls" style="display: none;"
                                                            onchange="this.form.submit()">
                                                    </form>

                                                     <a href="{{ route('admin.ipe.refund') }}" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to process refunds for all failed {{ $refund_count }} transactions?');">
                                                        <i class="las la-exchange-alt"></i> Refund
                                                        <span class="rounded">({{ $refund_count }})</span>
                                                    </a>

                                                    <div class="w-100 d-sm-block d-md-inline mt-2">
                                                        <span class="text-success d-block">
                                                            ✅ Response Code 200: Success
                                                        </span>
                                                        <span class="text-warning d-block">
                                                            ⚠️ Response Code 400: Failed
                                                        </span>
                                                        <span class="text-danger d-block">
                                                            ❗ All fields in the Excel file must be filled out
                                                        </span>
                                                    </div>

                                                </div>

                                                <form action="{{ route('admin.ipe.index') }}" method="GET">
                                                    <div class="row g-2">
                                                        <div class="col-md-3">
                                                            <input type="text" name="search" class="form-control"
                                                                value="{{ request('search') }}"
                                                                placeholder="Search by Tracking ID "
                                                                autocomplete="off">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <input type="date" name="date_from" class="form-control"
                                                                value="{{ request('date_from') }}"
                                                                placeholder="Start Date">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <input type="date" name="date_to" class="form-control"
                                                                value="{{ request('date_to') }}" placeholder="End Date">
                                                        </div>

                                                        <div class="col-md-2">
                                                            <select name="per_page" class="form-select">
                                                                <option value="5"
                                                                    {{ request('per_page') == 5 ? 'selected' : '' }}>5 per
                                                                    page</option>
                                                                <option value="10"
                                                                    {{ request('per_page') == 10 ? 'selected' : '' }}>10
                                                                    per page</option>
                                                                <option value="25"
                                                                    {{ request('per_page') == 25 ? 'selected' : '' }}>25
                                                                    per page</option>
                                                                <option value="50"
                                                                    {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                                                    per page</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <button type="submit"
                                                                class="btn btn-primary w-100">Filter</button>
                                                        </div>
                                                    </div>
                                                </form>


                                            </div>

                                            @if (!$ipeRequests->isEmpty())
                                                @php
                                                    // Calculate serial number based on pagination
                                                    $currentPage = $ipeRequests->currentPage(); // Current page number
                                                    $perPage = $ipeRequests->perPage(); // Number of items per page
                                                    $serialNumber = ($currentPage - 1) * $perPage + 1; // Starting serial number for the current page
                                                @endphp

                                                <div class="table-responsive">
                                                    <table class="table text-nowrap"
                                                        style="background:#fafafc !important">
                                                        <thead>
                                                            <tr>
                                                                <th width="5%" class="cust2"
                                                                    scope="col">
                                                                    ID</th>

                                                                <th class="cust2 ">Tracking ID</th>
                                                                <th scope="col" class="cust2 ">Reply
                                                                </th>
                                                                <th scope="col" class="cust2 ">Date</th>
                                                                <th scope="col" class="text-center ">
                                                                    Status</th>


                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($ipeRequests as $data)
                                                                <tr>
                                                                    <th scope="row">{{ $serialNumber++ }}</th>
                                                                    <td>{{ $data->trackingId }}</td>
                                                                    <td>{!! $data->reply !!}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($data->created_at)->format('d/m/Y h:i A') }}
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if ($data->resp_code == '200')
                                                                            <span
                                                                                class="badge bg-success">Sucessful</span>
                                                                        @elseif ($data->resp_code == '400')
                                                                            <span
                                                                                class="badge bg-danger">Failed</span>
                                                                        @elseif ($data->resp_code == '100')
                                                                            <span
                                                                                class="badge bg-warning">Pending</span>
                                                                        @else
                                                                            <span class="badge bg-primary">
                                                                                Processing
                                                                            </span>
                                                                        @endif
                                                                    </td>


                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>

                                                    <!-- Pagination Links -->
                                                    <div class="d-flex justify-content-center">
                                                        {{ $ipeRequests->links('vendor.pagination.bootstrap-5') }}
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center">
                                                    <img width="65%"
                                                        src="{{ asset('assets/images/no-transaction.gif') }}"
                                                        alt="No Requests Available">
                                                    <p class="fw-semibold fs-15">No Request Available!</p>
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

