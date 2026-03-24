@extends('layouts.dashboard')

@section('title', 'MOD IPE Clearance')
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
                                    Total Modification IPE Clearance Requests
                                </h6>

                                <div class="row g-2 justify-content-center">
                                    @php
                                        $validationStats = [
                                            [
                                                'label' => 'All',
                                                'value' => $totalAll,
                                                'bg' => '#f8f9fa',
                                                'text' => 'text-dark',
                                                'border' => 'border',
                                            ],
                                            [
                                                'label' => 'Pending',
                                                'value' => $totalInProgress,
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

                        <h4 class="card-title">Modification IPE Clearance</h4>
                        <p class="card-description">Submit NIN Modification IPE Clearance request with Tracking ID and NIN
                            for
                            assistance.</p>

                        <div class="row">
                            <div class="col-md-12 mb-3" role="tabpanel" aria-labelledby="new-tab">

                                <center>
                                    <img class="img-fluid" src="{{ asset('assets/images/img/nimc.png') }}" width="30%">
                                </center>
                                <center>
                                    <small class="font-italic text-danger"><i>Please note that this request will be
                                            processed within 24hrs. We appreciate your patience
                                            and
                                            will keep you updated on the status.
                                        </i>
                                    </small>
                                </center>

                                <div class="row text-center">
                                    <div class="col-md-12">
                                        <form id="form" name="nin-request" method="POST"
                                            action="{{ route('user.nin.mod.ipe.request') }}" enctype="multipart/form-data">
                                            @csrf

                                            <div class="row">
                                                <div class="col-md-12 mt-3 mb-3">
                                                    <select name="service" id="service" class="form-select text-dark"
                                                        required>
                                                        <option value="">-- Service Type --</option>
                                                        @foreach ($services as $service)
                                                            <option value="{{ $service->service_code }}">
                                                                {{ $service->name }} -
                                                                &#x20A6;{{ number_format($service->amount, 2) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-12">
                                                    <p class="mb-2 form-label" id="modify_lbl"></p>
                                                    <div id="input-container"></div>
                                                </div>


                                                <button type="submit" id="nin-request" class="btn btn-primary">
                                                    <i class="las la-share"></i> Submit Request
                                                </button>
                                            </div>

                                        </form>
                                    </div>

                                </div>

                            </div>


                            <div class="col-md-12" role="tabpanel" aria-labelledby="history-tab">
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
                                <form method="GET" action="{{ route('user.nin.mod.ipe.request') }}"
                                    class="row g-2 mb-3 mt-2 align-items-end">

                                    <div class="row g-2">
                                        <div class="col-md-3">
                                            <label for="search" class="form-label d-block d-md-none">Search</label>
                                            <input type="text" id="search" name="search" class="form-control"
                                                value="{{ request('search') }}" placeholder="Search Here ...">
                                        </div>

                                        <div class="col-md-3">
                                            <label for="date_from" class="form-label d-block d-md-none">Start
                                                Date</label>
                                            <input type="date" id="date_from" name="date_from" class="form-control"
                                                value="{{ request('date_from') }}" placeholder="Start Date">
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
                                @if (!$ninServices->isEmpty())

                                    @php
                                        $currentPage = $ninServices->currentPage();
                                        $perPage = $ninServices->perPage();
                                        $serialNumber = ($currentPage - 1) * $perPage + 1;
                                    @endphp

                                    <!-- DESKTOP TABLE VIEW -->
                                    <div class="table-responsive">
                                        <table class="table text-nowrap" style="background:#fafafc !important">
                                            <thead>
                                                <tr class="table-primary">
                                                    <th width="5%">ID</th>
                                                    <th>NIN Number</th>
                                                    <th>Tracking No</th>
                                                    <th class="text-center">Status</th>
                                                    <th>Date</th>
                                                    <th>Response</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($ninServices as $data)
                                                    <tr>
                                                        <th>{{ $serialNumber++ }}</th>
                                                        <td>{{ $data->nin_number }}</td>
                                                        <td>{{ $data->tracking_id }}</td>
                                                        <td class="text-center">
                                                            @if ($data->status == 'resolved')
                                                                <span class="badge bg-success">Sucessful</span>
                                                            @elseif ($data->status == 'rejected')
                                                                <span class="badge bg-danger">Failed</span>
                                                            @elseif ($data->status == 'pending')
                                                                <span class="badge bg-warning">Pending</span>
                                                            @else
                                                                <span class="badge bg-primary">
                                                                    Processing
                                                                </span>
                                                            @endif

                                                        </td>
                                                        <td>{{ $data->created_at }}</td>
                                                        <td>{!! $data->reason !!}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <!-- Pagination Links -->
                                        <div class="d-flex justify-content-center">
                                            {{ $ninServices->links('vendor.pagination.bootstrap-4') }}
                                        </div>
                                    </div>
                                @else
                                    <center>
                                        <img width="65%" src="{{ asset('assets/images/no-transaction.gif') }}"
                                            alt="">
                                    </center>
                                    <p class="text-center fw-semibold fs-15">No Request Available!</p>
                                @endif

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
            const form = document.getElementById('form');
            const submitButton = document.getElementById('nin-request');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerText = 'Please wait while we process your request...';
            });
        });

        $(document).ready(function() {
            hide();

            $("#service").change(function() {
                const selectedItem = this.value;

                // Clear dynamic content area
                $("#input-container").empty();
                $("#modify_lbl").text("").hide();

                let labelText = "";
                let inputs = '';

                switch (selectedItem) {
                    case '135':

                        labelText = "Enter NIN/Trcaking No";
                        inputs += createInput('nin', 'Enter NIN Number', 11, 'text', '^\\d{11}$',
                            'NIN must be 11 digits');
                        inputs += createInput('tracking_no', 'Enter Tracking Number', 15, 'text', '',
                            'Tracking Number must be 15 digits');
                        break;

                    default:
                        break;
                }

                $("#modify_lbl").text(labelText).show();
                $("#input-container").append(inputs);
            });
        });

        function hide() {
            $("#modify_lbl").hide();
        }

        function createInput(id, placeholder, maxlength = '', type = 'text', pattern = '', title = '', required =
            'required') {
            const max = maxlength ? `maxlength="${maxlength}"` : '';
            const pat = pattern ? `pattern="${pattern}"` : '';
            const tip = title ? `title="${title}"` : '';


            return `<input type="${type}" name="${id}" id="${id}" ${max} ${pat} ${tip} class="form-control mb-2" placeholder="${placeholder}" ${required} />`;
        }
    </script>
@endpush
