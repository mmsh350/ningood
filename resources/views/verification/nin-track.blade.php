@extends('layouts.dashboard')

@section('title', 'NIN Personalize')
@push('styles')
    <style>
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            z-index: 9999;
        }

        #overlay button {
            margin-top: 20px;
            padding: 10px 20px;
            background: #ff5252;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .pagination .page-link {
            min-width: 36px;
            text-align: center;
        }

        @media (max-width: 576px) {
            .pagination {
                font-size: 0.75rem;
            }
        }

        .border:hover {
            box-shadow: 0 0 6px rgba(0, 0, 0, 0.1);
            transform: scale(1.02);
            transition: all 0.2s ease-in-out;
        }

        .small-card {
            border-radius: 0.5rem;
            font-size: 0.875rem;
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
                    <div class="card">

                        <div class="card-body ">
                            <div class="col-12 mb-3">
                                <div class="mb-2">
                                    <h6 class="text-center text-uppercase text-muted fw-semibold mb-3"
                                        style="font-size: 0.85rem;">
                                        Total NIN Personalization Requests
                                    </h6>

                                    <div class="row g-2 justify-content-center">
                                        @php
                                            $personalizationStats = [
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

                                        @foreach ($personalizationStats as $stat)
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
                            <div class="alert alert-danger alert-dismissible text-center" id="errorMsg"
                                style="display:none;" role="alert">
                                <small id="message">Processing your request.</small>
                            </div>

                            <div class="row text-center">
                                <div class="col-md-12">
                                    <form action="{{ route('user.sendPersonalize') }}" name="personalize" method="POST">
                                        @csrf
                                        <div class="mb-3 row">
                                            <div class="col-md-12 mx-auto">
                                            </div>
                                            <div class="col-md-12 ">
                                                <p class="mb-2 text-muted">Tracking Number</p>
                                                <input type="text" id="nin" name="trackingId" value=""
                                                    class="form-control text-center" placeholder="Enter Tracking ID"
                                                    maxlength="15" required />
                                            </div>
                                        </div>
                                        <small class="text-danger">Our Personalization request process is fully automated.
                                            You
                                            can track the status of your request using the 'Check Status' button
                                        </small><br />
                                        <p class="fw-bold mt-2"> Service Fee:
                                            &#x20A6;{{ number_format($ServiceFee->amount), 2 }}</p>
                                        <button type="submit" class="btn btn-primary"><i class="lar la-check-circle"></i>
                                            Personalize</button>
                                    </form>


                                </div>

                                <div class="col-md-12 col-12">
                                    <form method="GET" action="{{ route('user.personalize-nin') }}"
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
                                                <label for="date_to" class="form-label d-block d-md-none">End Date</label>
                                                <input type="date" id="date_to" name="date_to" class="form-control"
                                                    value="{{ request('date_to') }}" placeholder="End Date">
                                            </div>

                                            <div class="col-md-3">
                                                <span class="form-label d-block d-md-none">&nbsp;</span>
                                                {{-- spacing for alignment --}}
                                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                            </div>
                                        </div>
                                    </form>

                                    @if (!$personalize->isEmpty())
                                        @php
                                            $currentPage = $personalize->currentPage();
                                            $perPage = $personalize->perPage();
                                        @endphp


                                        <div>
                                            <div class="table-responsive">
                                                <table class="table text-nowrap" style="background:#fafafc !important">
                                                    <thead>
                                                        <tr class="table-primary">
                                                            <th width="5%">ID</th>
                                                            <th>Date</th>
                                                            <th>Tracking No.</th>
                                                            <th>Name</th>
                                                            <th>NIN</th>
                                                            <th>Comments</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($personalize as $index => $data)
                                                            <tr>
                                                                <th scope="row">
                                                                    {{ ($currentPage - 1) * $perPage + $index + 1 }}</th>
                                                                <td>{{ $data->created_at }}</td>
                                                                <td>{{ strtoupper($data->tracking_no) }}</td>
                                                                <td>{{ $data->name }}</td>
                                                                <td>{{ $data->nin }}</td>
                                                                 <td>@if($data->status == 'Failed')
                                                                     {{ str_replace('"', ' ', $data->reply) }}
                                                                    @endif
                                                                 </td>
                                                                <td>
                                                                    @if ($data->status == 'Pending')
                                                                        <span class="badge bg-warning">Pending</span>
                                                                    @elseif($data->status == 'Successful')
                                                                        <span class="badge bg-success">Successful</span>
                                                                    @elseif($data->status == 'In-Progress')
                                                                        <span class="badge bg-warning">Processing</span>
                                                                    @else
                                                                        <span class="badge bg-danger">Failed</span>
                                                                    @endif
                                                                </td>

                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- ✅ Pagination -->
                                        <div class="d-flex justify-content-center mt-4">
                                            {{ $personalize->links('vendor.pagination.bootstrap-4') }}
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
    <div id="overlay">
        <div class="text-center">
            <p>To use this page, pop-ups must be enabled. Please allow pop-ups for this site.</p>
            <button onclick="enablePopups()">Allow Pop-ups</button>
        </div>
    </div>
    <div id="responsive-overlay"></div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/nin-track.js') }}"></script>
    <script>
        function enablePopups() {
            const testPopup = window.open('', '_blank', 'width=1,height=1');
            if (testPopup === null || typeof testPopup === 'undefined') {
                alert("Pop-ups are still blocked. Please allow pop-ups in your browser settings.");
            } else {

                testPopup.close();
                localStorage.setItem('popupsAllowed', 'true');
                document.getElementById('overlay').style.display = 'none';
                window.location.reload();
            }
        }
        window.onload = function() {
            if (localStorage.getItem('popupsAllowed') === 'true') {
                document.getElementById('overlay').style.display = 'none';
                return;
            }
            const testPopup = window.open('', '_blank', 'width=1,height=1');
            if (testPopup === null || typeof testPopup === 'undefined') {
                document.getElementById('overlay').style.display = 'flex';
            } else {
                testPopup.close();
                localStorage.setItem('popupsAllowed', 'true');
                document.getElementById('overlay').style.display = 'none';
            }
        };
    </script>
@endpush
