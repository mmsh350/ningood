@extends('layouts.dashboard')

@section('title', 'API Documentation')

@section('content')
<div class="row">
    <div class="mb-3 mt-1">
        <h4 class="mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }} 👋</h4>
        <p class="mb-0">Here’s a quick look at your dashboard.</p>
    </div>

    @include('common.message')

    <div class="col-lg-12 grid-margin d-flex flex-column">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card p-4">
                    <div class="row align-items-center">


                        <!-- Profile Update Form -->
                        <div class="col-md-8">

                        <div class="col mt-3">

                        <h6 class="mb-3">API Token:</h6>

                        <div class="input-group">
                            <input type="text" class="form-control bg-light" id="apiToken"
                                value="{{ Auth::user()->api_token }}" readonly>
                            <button type="button" class="btn btn-outline-primary" onclick="copyToken()">Copy</button>
                        </div>

                        <form action="{{ route('user.regenerate.token') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn text-light" style="background:#b11d24">Request New</button>
                        </form>

                        </div>
                        </div>

<div class="col-12 mt-4">
    <div class="card p-4">
        <h5 class="mb-3">API Documentation</h5>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="apiDocsTab" role="tablist">
             <li class="nav-item">
                <a class="nav-link active" id="auth-tab" data-bs-toggle="tab" href="#auth"
                   role="tab">Authentication</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="ipe-tab" data-bs-toggle="tab" href="#ipe"
                   role="tab">IPE Request & Status</a>
            </li>

            <li class="nav-item">
    <a class="nav-link" id="nin-tab" data-bs-toggle="tab" href="#nin"
       role="tab">NIN Validation</a>
</li>


            <li class="nav-item">
                <a class="nav-link" id="errors-tab" data-bs-toggle="tab" href="#errors"
                   role="tab">Error Codes</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content border border-top-0 p-4">

              <!-- AUTH TAB -->
            <div class="tab-pane fade show active" id="auth" role="tabpanel">
                <h6>Authentication</h6>

                <p>
                    All API requests require an API token.
                    Include the token in the <strong>Authorization</strong> header.
                </p>

                <pre class="bg-light p-2">
                   Authorization: Bearer {{ Auth::user()->api_token }}
                </pre>

                <p>
                    You can regenerate your token anytime using the
                    <strong>Request New</strong> button above.
                </p>
            </div>
            <!-- IPE TAB -->
            <div class="tab-pane fade " id="ipe" role="tabpanel">

                <h6>1. Submit IPE Request</h6>

                <p><strong>Endpoint</strong></p>
                <pre class="bg-light p-2">POST /api/ipe</pre>

                <p><strong>Headers</strong></p>
                <pre class="bg-light p-2">
Authorization: Bearer YOUR_API_TOKEN
Accept: application/json
Content-Type: application/json
                </pre>

                <p><strong>Request Body</strong></p>
                <pre class="bg-light p-2">
{
    "trackingId": "ABC123DEF456XYZ"
}
                </pre>

                <p><strong>Successful Response</strong></p>
                <pre class="bg-light p-2">
{
    "status": true,
    "code": "IPE_SUBMITTED",
    "message": "IPE request submitted successfully",
    "data": {
        "tracking_id": "ABC123DEF456XYZ",
        "service_fee": 250,
        "balance_left": 1750,
        "pricing_type": "custom"
    }
}
                </pre>

                <hr>

                <h6>2. Check IPE Status</h6>

                <p><strong>Endpoint</strong></p>
                <pre class="bg-light p-2">POST /api/ipe-status</pre>

                <p><strong>Request Body</strong></p>
                <pre class="bg-light p-2">
{
    "trackingId": "ABC123DEF456XYZ"
}
                </pre>

                <p><strong>Status: Pending</strong></p>
                <pre class="bg-light p-2">
{
    "status": true,
    "code": "PENDING",
    "message": "IPE request is still being processed"
}
                </pre>

                <p><strong>Status: Successful</strong></p>
                <pre class="bg-light p-2">
{
    "status": true,
    "code": "SUCCESSFUL",
    "message": "IPE request completed successfully",
    "data": {
        "reply": "NIN verified successfully"
    }
}
                </pre>

                <p><strong>Status: Failed (Refunded)</strong></p>
                <pre class="bg-light p-2">
{
    "status": false,
    "code": "FAILED",
    "message": "IPE request failed and refunded"
}
                </pre>

            </div>
<!-- NIN VALIDATION TAB -->
<div class="tab-pane fade" id="nin" role="tabpanel">

    <h6>1. Submit NIN Validation Request</h6>

    <p><strong>Endpoint</strong></p>
    <pre class="bg-light p-2">POST /api/nin-validation</pre>

    <p><strong>Headers</strong></p>
    <pre class="bg-light p-2">
Authorization: Bearer YOUR_API_TOKEN
Accept: application/json
Content-Type: application/json
    </pre>

    <p><strong>Request Body</strong></p>
    <pre class="bg-light p-2">
{
    "message": "Record not found",
    "nin": "12345678901"
}
    </pre>

    <p><strong>Successful Response</strong></p>
    <pre class="bg-light p-2">
{
    "status": true,
    "code": "REQUEST_SUBMITTED",
    "message": "NIN validation request submitted successfully",
    "data": {
        "service": "NIN Validation",
        "service_fee": 150,
        "balance_left": 850,
        "pricing_type": "custom"
    }
}
    </pre>

    <hr>

    <h6>2. Check NIN Validation Status</h6>

    <p><strong>Endpoint</strong></p>
    <pre class="bg-light p-2">POST /api/nin-validation-status</pre>

    <p><strong>Request Body</strong></p>
    <pre class="bg-light p-2">
{
    "nin": "12345678901"
}
    </pre>

    <p><strong>Status: Pending / In-Progress</strong></p>
    <pre class="bg-light p-2">
{
    "status": true,
    "code": "PENDING",
    "message": "NIN validation is still being processed",
    "data": {
        "current_status": "In-Progress"
    }
}
    </pre>

    <p><strong>Status: Successful</strong></p>
    <pre class="bg-light p-2">
{
    "status": true,
    "code": "SUCCESSFUL",
    "message": "NIN validation completed successfully",
    "data": {
        "nin": "12345678901",
        "reference": "TRX123456789",
        "reply": "NIN verified successfully"
    }
}
    </pre>

    <p><strong>Status: Failed</strong></p>
    <pre class="bg-light p-2">
{
    "status": false,
    "code": "FAILED",
    "message": "NIN validation failed",
    "data": {
        "nin": "12345678901",
        "reference": "TRX123456789",
        "reply": "NIN not found"
    }
}
    </pre>

    <p class="mt-3 text-muted">
        <strong>Note:</strong> No refunds are processed via the status endpoint.
        This endpoint is strictly for checking request status.
    </p>

</div>

            <!-- ERRORS TAB -->
            <div class="tab-pane fade" id="errors" role="tabpanel">
                <h6>Common Error Codes</h6>

               <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="min-width: 180px;">Error Code</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>SERVICE_DISABLED</code></td>
                <td>Service is currently unavailable</td>
            </tr>
            <tr>
                <td><code>INSUFFICIENT_BALANCE</code></td>
                <td>Wallet balance is insufficient</td>
            </tr>
            <tr>
                <td><code>DUPLICATE_REQUEST</code></td>
                <td>Tracking ID already submitted</td>
            </tr>
            <tr>
                <td><code>NOT_FOUND</code></td>
                <td>Tracking ID does not exist</td>
            </tr>
            <tr>
                <td><code>SERVER_ERROR</code></td>
                <td>Unexpected server error</td>
            </tr>
            <tr>
    <td><code>VALIDATION_ERROR</code></td>
    <td>Invalid request parameters</td>
</tr>
<tr>
    <td><code>WALLET_NOT_FOUND</code></td>
    <td>User wallet not found</td>
</tr>
<tr>
    <td><code>REQUEST_SUBMITTED</code></td>
    <td>NIN validation request submitted successfully</td>
</tr>

        </tbody>
    </table>
</div>

            </div>

        </div>
    </div>
</div>


                    </div> <!-- end row -->
                </div> <!-- end card -->
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
    <script>
        function copyToken() {
            let tokenInput = document.getElementById("apiToken");
            tokenInput.select();
            tokenInput.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(tokenInput.value);
            alert("API Token copied!");
        }
    </script>
