@extends('layouts.dashboard')

@section('title', 'NIN IPE Request')

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
        <div class="card">
            <div class="card-body">

                <!-- Metrics -->
                <div class="col-12 mb-3">
                    <div class="row g-2 justify-content-center">
                        @php
                            $metrics = [
                                ['label'=>'All', 'value'=>$totalAll, 'bg'=>'#f8f9fa','text'=>'text-dark','border'=>'border'],
                                ['label'=>'Pending', 'value'=>$totalPending, 'bg'=>'#fff3cd','text'=>'text-dark','border'=>'border-warning'],
                                ['label'=>'Failed', 'value'=>$totalFailed, 'bg'=>'#f8d7da','text'=>'text-danger','border'=>'border-danger'],
                                ['label'=>'Successful', 'value'=>$totalSuccessful, 'bg'=>'#d1e7dd','text'=>'text-success','border'=>'border-success'],
                            ];
                        @endphp

                        @foreach ($metrics as $metric)
                        <div class="col-6 col-sm-3 col-lg-2">
                            <div class="border rounded-3 text-center py-2 px-1 shadow-sm {{ $metric['text'] }}" style="background: {{ $metric['bg'] }}; font-size:0.85rem;">
                                <div class="small fw-light mb-1">{{ $metric['label'] }}</div>
                                <div class="fw-bold" style="font-size:1.1rem;">{{ $metric['value'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <h4 class="card-title">IPE CLEARANCE for new tracking ID</h4>
                <p class="card-description">Send your IPE request to get your tracking number</p>
                <div class="alert alert-warning shadow-sm text-dark">
                    Refunds for failed IPE requests are processed automatically.
                </div>

                <!-- Display Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <!-- Validation Summary -->
                <div class="col-12 mb-3">
                    <div class="row g-2 justify-content-center">
                        <div class="col-4 col-sm-2">
                            <div class="border rounded p-2 bg-light text-center">
                                <small class="text-muted">Total</small>
                                <div class="fw-bold" id="totalCount">0</div>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="border rounded p-2 bg-success-subtle text-center">
                                <small class="text-muted">Valid</small>
                                <div class="fw-bold text-success" id="validCount">0</div>
                            </div>
                        </div>
                        <div class="col-4 col-sm-2">
                            <div class="border rounded p-2 bg-danger-subtle text-center">
                                <small class="text-muted">Invalid</small>
                                <div class="fw-bold text-danger" id="invalidCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" id="singleTabBtn" data-bs-toggle="tab"
                            data-bs-target="#singleTab" type="button">Single Request</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="bulkTabBtn" data-bs-toggle="tab"
                            data-bs-target="#bulkTab" type="button">Bulk Upload</button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- SINGLE REQUEST -->
                    <div class="tab-pane fade show active" id="singleTab">
                        <form id="singleForm" method="POST" action="{{ route('user.ipe-request') }}">
                            @csrf
                            <label class="fw-semibold">Tracking ID</label>
                            <input type="text" name="trackingId" id="singleTracking"
                                class="form-control text-center"
                                maxlength="15"
                                placeholder="Enter 15-digit Tracking ID" required>

                            <div class="mt-2 small text-muted">
                                ₦{{ number_format($ServiceFee, 2) }} per tracking ID
                            </div>

                            <button class="btn btn-primary mt-3 w-100" id="singleSubmit" disabled>
                                Submit Request
                            </button>
                        </form>
                    </div>

                    <!-- BULK REQUEST -->
                    <div class="tab-pane fade" id="bulkTab">
                        <form id="bulkForm" method="POST" action="{{ route('user.ipe-bulk-request') }}">
                            @csrf
                            <label class="fw-semibold">Tracking IDs (One per line)</label>
                            <textarea name="trackingIds" id="bulkTracking" rows="6"
                                class="form-control text-center"
                                placeholder="ABC123456789012
XYZ987654321000"></textarea>

                            <div class="mt-2 small text-muted">
                                ₦{{ number_format($ServiceFee, 2) }} each
                            </div>

                            <button class="btn btn-primary mt-3 w-100" id="bulkSubmit" disabled>
                                Submit Bulk Request
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- IPE Table / Pagination -->
    <div class="col-md-12 mt-4">
        @if (!$ipes->isEmpty())
            @php
                $currentPage = $ipes->currentPage();
                $perPage = $ipes->perPage();
            @endphp
            <div class="table-responsive">
                <table class="table text-nowrap" style="background:#fafafc !important">
                    <thead>
                        <tr class="table-primary">
                            <th>ID</th>
                            <th>Tracking ID</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ipes as $index => $data)
                        <tr>
                            <td>{{ ($currentPage - 1) * $perPage + $index + 1 }}</td>
                            <td>{{ $data->trackingId }}</td>
                            <td>

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
                            <td>{{ $data->created_at }}</td>
                            <td class="text-center">{!! $data->reply !!}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-4">
                {{ $ipes->links('vendor.pagination.bootstrap-4') }}
            </div>
        @else
            <div class="text-center py-5">
                <img width="65%" src="{{ asset('assets/images/no-transaction.gif') }}" alt="No transactions">
                <p class="text-center fw-semibold fs-5 mt-3">No Requests Available!</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const updateCounts = (items) => {
        const total = items.length;
        const valid = items.filter(i => /^[A-Za-z0-9]{15}$/.test(i)).length;
        const invalid = total - valid;

        document.getElementById('totalCount').innerText = total;
        document.getElementById('validCount').innerText = valid;
        document.getElementById('invalidCount').innerText = invalid;

        return invalid === 0 && total > 0;
    };

    // Single
    const singleInput = document.getElementById('singleTracking');
    const singleSubmit = document.getElementById('singleSubmit');
    singleInput.addEventListener('input', () => {
        const items = [singleInput.value.trim()];
        singleSubmit.disabled = !updateCounts(items);
    });

    // Bulk
    const bulkInput = document.getElementById('bulkTracking');
    const bulkSubmit = document.getElementById('bulkSubmit');
    bulkInput.addEventListener('input', () => {
        const items = bulkInput.value.split('\n').map(i => i.trim()).filter(i => i !== '');
        bulkSubmit.disabled = !updateCounts(items);
    });

    // Disable button on submit
    const forms = [document.getElementById('singleForm'), document.getElementById('bulkForm')];
    forms.forEach(f => {
        f.addEventListener('submit', () => {
            f.querySelector('button[type="submit"]').disabled = true;
            f.querySelector('button[type="submit"]').innerText = 'Processing...';
        });
    });
});
</script>
@endpush

