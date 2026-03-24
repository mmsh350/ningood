@extends('layouts.dashboard')

@section('title', 'Generate TIN')
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
                        <div class="card-header">
                            <div class="card-title m-0">
                                Generate TIN
                            </div>
                        </div>
                        <div class="card-body ">
                            <div class="alert alert-warning border-1 bg-light-warning d-flex align-items-start gap-3 p-3 rounded-2">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="bi bi-info-circle-fill text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading fw-bold text-dark mb-2">Verification Fee Notice</h6>
                                    <p class="mb-2 small text-danger">
                                        <strong>Note:</strong>   A <strong>₦{{ number_format($ServiceFee->amount, 2) }}</strong> verification fee will be charged from your wallet upon successful TIN generation.
                                    </p>
                                </div>
                            </div>

                            <div class="alert alert-danger alert-dismissible text-center" id="errorMsg"
                                style="display:none;" role="alert">
                                <small id="message">Processing your request.</small>
                            </div>
                            <div class="row text-center">
                                <div class="col-md-12">
                                    <form id="verifyForm" name="verifyForm" method="POST">
                                        @csrf
                                        <div class="mb-3 row">
                                            <div class="col-md-12 d-flex justify-content-center flex-column align-items-center">
                                                <p class="mb-2 text-muted text-center">Generate TIN Number</p>
                                                <div class="btn-group mb-3" role="group" aria-label="Entity type">
                                                    <input type="radio" class="btn-check" name="entity" id="entityIndividual" autocomplete="off" value="individual" checked>
                                                    <label class="btn btn-outline-primary" for="entityIndividual">Individual (NIN)</label>

                                                    <input type="radio" class="btn-check" name="entity" id="entityCorporate" autocomplete="off" value="corporate">
                                                    <label class="btn btn-outline-primary" for="entityCorporate">Corporate (RC)</label>
                                                </div>
                                            </div>

                                            <div class="col-md-8 col-lg-6 mx-auto" id="individualFields">
                                                <div class="mb-2">
                                                    <label class="form-label">NIN</label>
                                                    <div class="input-group">
                                                       
                                                        <input type="text" id="nin" name="nin" value="" class="form-control" maxlength="11" placeholder="e.g. 44516677277" />
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">First Name</label>
                                                    <input type="text" id="firstName" name="firstName" class="form-control" placeholder="First name" />
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Last Name</label>
                                                    <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Last name" />
                                                </div>
                                                <div>
                                                    <label class="form-label">Date of Birth</label>
                                                    <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="col-md-8 col-lg-6 mx-auto" id="corporateFields" style="display:none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Entity Type</label>
                                                    <select id="type" name="type" class="form-select text-dark">
                                                        <option value="1">Business Name</option>
                                                        <option value="2">Company</option>
                                                        <option value="3">Incorporated Trustee</option>
                                                        <option value="4">Limited Partnership</option>
                                                        <option value="5">Limited Liability Partnership</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="rc">RC Number</label>
                                                    <div class="input-group">
                                                        <input type="text" id="rc" name="rc" value="" class="form-control" placeholder="e.g. 8891227" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <button type="submit" id="verifyNIN" style="background:#2563eb; transition: all 0.3s ease;" class="btn text-light hover:bg-primary-700 shadow-sm"><i class="lar la-check-circle"></i> Generate TIN</button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-12 mt-2">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title m-0">
                                <i class="ri-user-search-line fw-bold"></i> TIN Information
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12  row">
                                <div class="alert alert-danger alert-dismissible text-center" id="errorMsg2"
                                    style="display:none;" role="alert">
                                    <small id="message2">Processing your request.</small>
                                </div>
                                <div class="validation-info col-md-12 mb-2 hidden" id="validation-info">
                                    <center>
                                        <img src="{{ asset('assets/images/search.png') }}" width="20%" alt="Search Icon">
                                        <p class="mt-5">This section will display search results </p>
                                    </center>
                                </div>
                                <div class="col-md-12">
                                    <div class="btn-list text-center" style="display:none;" id="download">
                                        <div class="mb-2 mr-2">
                                            <a href="#" id="download_id" type="button"
                                                class="btn text-light" style="background:#2563eb; transition: all 0.3s ease;"><i class="bi bi-download"></i>&nbsp;
                                                Download TIN (&#x20A6;{{ $standard_tin_fee->amount }})</a>
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
    <div id="overlay">
        <div class="text-center">
            <p>To use this page, pop-ups must be enabled. Please allow pop-ups for this site.</p>
            <button onclick="enablePopups()">Allow Pop-ups</button>
        </div>
    </div>
    <div id="responsive-overlay"></div>
@endsection
@push('scripts')
                                      <script src="{{ asset('assets/js/tin.js') }}"></script>
                                      <script>
                                                (function(){
                                                    var ind = document.getElementById('entityIndividual');
                                                    var corp = document.getElementById('entityCorporate');
                                                    var individualFields = document.getElementById('individualFields');
                                                    var corporateFields = document.getElementById('corporateFields');
                                                    var typeSelect = document.getElementById('type');
                                                    var rcLabel = document.querySelector('label[for="rc"]');

                                                    function toggleEntity(){
                                                        var isCorp = corp.checked;
                                                        corporateFields.style.display = isCorp ? 'block' : 'none';
                                                        individualFields.style.display = isCorp ? 'none' : 'block';

                                                    }

                                                    function toggleLabel(){
                                                        if(typeSelect.value === '1'){
                                                            rcLabel.textContent = 'BN Number';
                                                        } else {
                                                            rcLabel.textContent = 'RC Number';
                                                        }
                                                    }

                                                    // No payload preview required — keep only toggle behavior

                                                    if(ind) ind.addEventListener('change', toggleEntity);
                                                    if(corp) corp.addEventListener('change', toggleEntity);
                                                    if(typeSelect) typeSelect.addEventListener('change', toggleLabel);

                                                    // initialize
                                                    toggleEntity();
                                                    toggleLabel();

                                                })();
                                            </script>
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
