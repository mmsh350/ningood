<?php

namespace App\Http\Controllers;

use App\Models\BankService;
use App\Models\BvnPhoneSearch;
use App\Models\NinModification;
use App\Models\NinValidation;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\UserServicePrice;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServicesController extends Controller
{
    protected $transactionService;

    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->user()->id;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);

        $query = Service::query();

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $query->where('service_code', 'NOT LIKE', '%\_%');

        $services = $query->paginate($perPage)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function edit($id)
    {

        $service = Service::findOrFail($id);

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'description' => 'nullable',
            'status' => 'required|in:enabled,disabled',
        ]);

        $service = Service::findOrFail($id);
        $service->update($request->all());

        return redirect()->route('admin.services.index')->with('success', 'Service Updated Successfully!');
    }

    public function ninModification()
    {

        $services = Service::where('type', 'nin_mod')->get();

        $ninServices = NinModification::where('user_id', $this->loginId)
            ->orderBy('id', 'desc')
            ->paginate(5);

        $consent = SiteSetting::first();

        return view('nin-mod', compact('services', 'ninServices', 'consent'));
    }

    public function requestModification(Request $request)
    {

        $request->validate([
            'nin' => 'required|digits:11',
            'firstname' => 'nullable|string',
            'middlename' => 'nullable|string',
            'surname' => 'nullable|string',
            'dob' => 'nullable|date',
            'phone' => 'nullable|digits_between:10,15',
            'address' => 'nullable|string|max:255',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'affidavit' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:2048',
            'full_address' => 'nullable|string',
            'origin_address' => 'nullable|string',
            'state' => 'nullable|string',
            'lga' => 'nullable|string',
            'education_qualification' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'father_full_name' => 'nullable|string',
            'father_state_of_origin' => 'nullable|string',
            'father_lga_of_origin' => 'nullable|string',
            'mother_full_name' => 'nullable|string',
            'mother_state_of_origin' => 'nullable|string',
            'mother_lga_of_origin' => 'nullable|string',
            'mother_maiden_name' => 'nullable|string',
        ]);

        $photoPath = null;
        $affidavitPath = null;

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $photoPath = $image->store('photos', 'public');
        }

        if ($request->hasFile('affidavit')) {
            $doc = $request->file('affidavit');
            $affidavitPath = $doc->store('affidavits', 'public');
        }

        // NIN Services Fee
        $ServiceFee = 0;

        $Service = Service::where('service_code', $request->input('service'))
            ->where('status', 'enabled')
            ->first();

        if (! $Service) {
            return redirect()->back()->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $Service->amount;
        $serviceType = $Service->name;
        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $balance = $wallet->balance - $ServiceFee;

            Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'NIN Modification Request', $serviceDesc, 'Wallet', 'Approved');

            $trx_id = $transaction->id;

            NinModification::create([
                'user_id' => $this->loginId,
                'tnx_id' => $trx_id,
                'refno' => $transaction->referenceId,
                'nin_number' => $request->nin,
                'address' => $request->address,
                'surname' => strtoupper($request->surname),
                'middle_name' => strtoupper($request->middlename),
                'description' => $serviceType,
                'first_name' => strtoupper($request->firstname),
                'phone_number' => $request->phone,
                'dob' => $request->dob,
                'photo' => $photoPath,
                'affidavit' => $affidavitPath,
                'full_address' => $request->full_address,
                'origin_address' => $request->origin_address,
                'state' => $request->state,
                'lga' => $request->lga,
                'education_qualification' => strtoupper($request->education_qualification),
                'marital_status' => strtoupper($request->marital_status),
                'father_full_name' => strtoupper($request->father_full_name),
                'father_state_of_origin' => strtoupper($request->father_state_of_origin),
                'father_lga_of_origin' => strtoupper($request->father_lga_of_origin),
                'mother_full_name' => strtoupper($request->mother_full_name),
                'mother_state_of_origin' => strtoupper($request->mother_state_of_origin),
                'mother_lga_of_origin' => strtoupper($request->mother_lga_of_origin),
                'mother_maiden_name' => strtoupper($request->mother_maiden_name),
            ]);

            return redirect()->back()->with('success', 'NIN Modification Service Request was successfully');
        }
    }

    public function ninServices(Request $request)
    {

        $now = Carbon::now();

        $services = Service::where('type', 'nin_services')
            ->where('status', 'enabled')
            ->get()
            ->map(function ($service) use ($now) {

                $userPrice = UserServicePrice::where('user_id', auth()->id())
                    ->where('service_id', $service->id)
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_from')
                            ->orWhere('valid_from', '<=', $now);
                    })
                    ->where(function ($q) use ($now) {
                        $q->whereNull('valid_to')
                            ->orWhere('valid_to', '>=', $now);
                    })
                    ->orderByDesc('id')
                    ->first();

                // Attach resolved price
                $service->price = $userPrice
                    ? $userPrice->custom_price
                    : $service->amount;

                $service->price_source = $userPrice ? 'custom' : 'default';

                return $service;
            });

        $query = NinValidation::where('user_id', auth()->id())
            ->where('tag', null);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nin_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $ninServices = $query->orderBy('id', 'desc')->paginate(10);

        // ✅ Status counts
        $statusCounts = NinValidation::selectRaw('status, COUNT(*) as count')
            ->where('user_id', auth()->user()->id)
            ->where('tag', null)
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAll = NinValidation::where('user_id', auth()->user()->id)->where('tag', null)->count();
        $totalPending = $statusCounts['Pending'] ?? 0;
        $totalFailed = $statusCounts['Failed'] ?? 0;
        $totalInProgress = $statusCounts['In-Progress'] ?? 0;
        $totalSuccessful = $statusCounts['Successful'] ?? 0;

        return view('nin-services', compact(
            'services',
            'ninServices',
            'totalAll',
            'totalPending',
            'totalFailed',
            'totalInProgress',
            'totalSuccessful'
        ));
    }

    public function requestNinService(Request $request)
    {
        $rules = [
            'service' => ['required', 'exists:services,service_code'],
        ];

        switch ($request->input('service')) {

            case '113':
            case '114':

                // NIN only
                $rules += [
                    'nin' => ['required', 'digits:11'],
                ];
                break;
        }

        $validated = $request->validate($rules);

        // NIN Services Fee
        $ServiceFee = 0;

        $Service = Service::where('service_code', $request->input('service'))
            ->where('status', 'enabled')
            ->first();

        if (! $Service) {
            return redirect()->back()->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $Service->amount;
        $serviceType = $Service->name;
        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        if (! $wallet) {
            return redirect()->back()->with('error', 'Wallet not found.');
        }

        $wallet_balance = $wallet->balance;
        $balance = 0;

        $now = Carbon::now();

        // Resolve price (custom → default)
        $userPrice = UserServicePrice::where('user_id', $this->loginId)
            ->where('service_id', $Service->id)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->orderByDesc('id')
            ->first();

        $ServiceFee = $userPrice
            ? $userPrice->custom_price
            : $Service->amount;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            try {

                // $url = env('BASE_API_URL_s8v').'/api/validation';
                // $token = env('API_TOKEN_s8v');

                // $data = ['nin' => $request->input('nin'), 'error' => $serviceType, 'api' => $token];

                // $headers = [
                //     'Accept: application/json, text/plain, */*',
                //     'Content-Type: application/json',
                // ];

                // // Initialize cURL
                // $ch = curl_init();

                // // Set cURL options
                // curl_setopt($ch, CURLOPT_URL, $url);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // curl_setopt($ch, CURLOPT_POST, true);
                // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // // Execute request
                // $response = curl_exec($ch);

                // // Check for cURL errors
                // if (curl_errno($ch)) {
                //     throw new \Exception('cURL Error: '.curl_error($ch));
                // }

                // Close cURL session
                // curl_close($ch);

                // $response = json_decode($response, true);

                // if (isset($response['status']) && $response['status'] === 'success') {
                $balance = $wallet->balance - $ServiceFee;

                Wallet::where('user_id', $this->loginId)
                    ->update(['balance' => $balance]);

                $serviceDesc = 'Wallet debited with a service fee of ₦'.number_format($ServiceFee, 2);

                $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'NIN Validation Request '.'('.$serviceType.')', $serviceDesc, 'Wallet', 'Approved');

                $trx_id = $transaction->id;

                NinValidation::create([
                    'user_id' => $this->loginId,
                    'tnx_id' => $trx_id,
                    'refno' => $transaction->referenceId,
                    'nin_number' => $request->nin,
                    'description' => $serviceType,
                ]);

                return redirect()->back()->with('success', 'Validation request has been submitted , kindly check status after 24 working hours');
                // } else {
                //     return redirect()->back()->with('error', 'Validation Request was not successfully');
                // }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'An error occurred while making the API request');
            }
        }
    }

    public function requestBulkNinService(Request $request)
    {
        $validated = $request->validate([
            'service' => ['required', 'exists:services,service_code'],
            'nins' => ['required', 'string'],
        ]);

        // Convert textarea to array
        $nins = collect(
            preg_split("/\r\n|\n|\r/", trim($validated['nins']))
        )
            ->map(fn ($nin) => trim($nin))
            ->filter()
            ->unique()
            ->values();

        if ($nins->isEmpty()) {
            return back()->with('error', 'No valid NINs provided.');
        }

        // Validate each NIN
        foreach ($nins as $nin) {
            if (! preg_match('/^\d{11}$/', $nin)) {
                return back()->with(
                    'error',
                    "Invalid NIN detected: {$nin}"
                );
            }
        }

        $service = Service::where('service_code', $validated['service'])
            ->where('status', 'enabled')
            ->first();

        if (! $service) {
            return back()->with('error', 'Service not available.');
        }

        // Resolve service price
        $now = Carbon::now();

        $userPrice = UserServicePrice::where('user_id', $this->loginId)
            ->where('service_id', $service->id)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->orderByDesc('id')
            ->first();

        $unitPrice = $userPrice
            ? $userPrice->custom_price
            : $service->amount;

        $totalCost = $unitPrice * $nins->count();

        // Wallet check
        $wallet = Wallet::where('user_id', $this->loginId)->first();

        if (! $wallet || $wallet->balance < $totalCost) {
            return back()->with(
                'error',
                'Insufficient wallet balance for bulk request.'
            );
        }

        DB::beginTransaction();

        try {

            // Insert validations
            foreach ($nins as $nin) {

                $wallet->update([
                    'balance' => $wallet->balance - $unitPrice,
                ]);
                // Create ONE transaction for bulk
                $description = "NIN Validation ({$nin})";

                $transaction = $this->transactionService->createTransaction(
                    $this->loginId,
                    $unitPrice,
                    $description,
                    'Wallet debited ₦'.number_format($unitPrice, 2),
                    'Wallet',
                    'Approved'
                );

                NinValidation::create([
                    'user_id' => $this->loginId,
                    'tnx_id' => $transaction->id,
                    'refno' => $transaction->referenceId,
                    'nin_number' => $nin,
                    'description' => $service->name,
                    'status' => 'Pending',
                ]);
            }

            DB::commit();

            return back()->with(
                'success',
                "{$nins->count()} NINs submitted successfully."
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with(
                'error',
                'Bulk submission failed. Please try again.'
            );
        }
    }

    public function ninRequestStatus($nin)
    {
        try {

            $url = env('BASE_API_URL_s8v').'/api/validation/status';
            $token = env('API_TOKEN_s8v');

            $data = ['nin' => $nin, 'token' => $token];

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['status']) && $response['status'] === 'Successful') {

                NinValidation::where('nin_number', $nin)
                    ->where('user_id', $this->loginId)
                    ->update(['reason' => $response['reply'] ?? 'N/A', 'status' => $response['status'], 'resp_code' => '200']);

                return redirect()->route('user.nin.services')
                    ->with('success', 'Validation request is successful, check the reply section');
            } elseif (isset($response['status']) && $response['status'] === 'Failed') {

                NinValidation::where('nin_number', $nin)
                    ->where('user_id', $this->loginId)
                    ->update(['reason' => $response['reply'] ?? null, 'status' => $response['status'], 'resp_code' => '400']);

                return redirect()->route('user.nin.services')
                    ->with('error', 'Validation Failed !');
            } elseif (isset($response['status']) && $response['status'] === 'New') {
                return redirect()->route('user.nin.services')
                    ->with('success', 'Validation request has been submitted , kindly check status after 24 working hours');
            } elseif (isset($response['status']) && $response['status'] === 'In-Progress') {

                NinValidation::where('nin_number', $nin)
                    ->where('user_id', $this->loginId)
                    ->update(['reason' => $response['reply'] ?? null, 'status' => $response['status']]);

                return redirect()->route('user.nin.services')
                    ->with('success', 'Validation Request In-Progress !');
            } else {
                return redirect()->route('user.nin.services')
                    ->with('error', 'Unexpected Network Error! Try Again..');
            }
        } catch (\Exception $e) {

            return redirect()->route('user.nin.services')
                ->with('error', 'An error occurred while making the API request');
        }
    }

    public function ninServicesList(Request $request)
    {

        // Services
        $pending = NinValidation::whereIn('status', ['Pending', 'In-Progress'])->whereNull('tag')
            ->count();

        $resolved = NinValidation::where('status', 'Successful')->whereNull('tag')
            ->count();

        $rejected = NinValidation::where('status', 'Failed')->whereNull('tag')
            ->count();

        $total_request = NinValidation::whereNull('tag')->count();

        $query = NinValidation::with(['user', 'transactions'])->whereNull('tag'); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('nin_number', 'like', "%{$searchTerm}%") // Search in NIN
                    ->orWhere('email', 'like', "%{$searchTerm}%") // Search by Email
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $nin_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'Pending' THEN 1
                    WHEN status = 'In-Progress' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'nin-services';

        return view('admin.nin-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'nin_services',
            'request_type'
        ));
    }

    public function delinkServicesList(Request $request)
    {

        // Services
        $pending = NinValidation::whereIn('status', ['Pending', 'In-Progress'])->where('tag', 'DELINK')
            ->count();

        $resolved = NinValidation::where('status', 'Successful')->where('tag', 'DELINK')
            ->count();

        $rejected = NinValidation::where('status', 'Failed')->where('tag', 'DELINK')
            ->count();

        $total_request = NinValidation::where('tag', 'DELINK')->count();

        $query = NinValidation::with(['user', 'transactions'])->where('tag', 'DELINK'); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('nin_number', 'like', "%{$searchTerm}%") // Search in NIN
                    ->orWhere('email', 'like', "%{$searchTerm}%") // Search by Email
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $nin_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'Pending' THEN 1
                    WHEN status = 'In-Progress' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'delink-services';

        return view('admin.delink-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'nin_services',
            'request_type'
        ));
    }

    public function showRequests($request_id, $type, $requests = null)
    {

        switch ($type) {
            case 'bvn-enrollment':

                break;
            case 'bvn-modification':

                break;
            case 'upgrade':

                break;

            case 'nin-services':
                $requests = NinValidation::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
                break;

            case 'delink-services':
                $requests = NinValidation::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'delink-services';
                break;

            case 'vnin-to-nibss':
                break;

            default:
                $requests = NinValidation::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
        }

        if (strtolower($requests->status) == 'Failed') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'admin.view-request',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $requestDetails = NinValidation::findOrFail($id);
        $route = 'admin.nin.services.list';

        if ($type === 'delink-services') {
            $route = 'admin.delink.services.list';
        }

        $status = $request->status;

        $requestDetails->status = $status;
        $requestDetails->reason = $request->comment;

        if ($request->status === 'Failed') {

            $requestDetails->refunded_at = Carbon::now();

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'NIN Service Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }

    public function bvnServicesList(Request $request)
    {

        // Services
        $pending = BvnPhoneSearch::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = BvnPhoneSearch::where('status', 'resolved')
            ->count();

        $rejected = BvnPhoneSearch::where('status', 'rejected')
            ->count();

        $total_request = BvnPhoneSearch::count();

        $query = BvnPhoneSearch::with(['user', 'transactions']); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('phone_number', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('name', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $bvn_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'bvn-services';

        return view('admin.bvn-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'bvn_services',
            'request_type'
        ));
    }

    public function showBvnRequests($request_id, $type, $requests = null)
    {

        switch ($type) {
            case 'bvn-enrollment':

                break;
            case 'bvn-modification':

                break;
            case 'upgrade':

                break;

            case 'nin-services':
                $requests = BvnPhoneSearch::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
                break;

            case 'vnin-to-nibss':

                break;

            default:
                $requests = BvnPhoneSearch::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
        }

        if (strtolower($requests->status) == 'rejected') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'admin.view-bvn-request',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateBvnRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $requestDetails = BvnPhoneSearch::findOrFail($id);
        $route = 'admin.bvn.services.list';
        $status = $request->status;

        $requestDetails->status = $status;
        $requestDetails->reason = $request->comment;

        if ($request->status === 'rejected') {

            $requestDetails->refunded_at = Carbon::now();

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'BVN Search Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }

    public function modServicesList(Request $request)
    {

        // Services
        $pending = ninModification::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = ninModification::where('status', 'resolved')
            ->count();

        $rejected = ninModification::where('status', 'rejected')
        ->count();

    $queried = ninModification::where('status', 'query')
        ->count();

    $total_request = ninModification::count();

        $query = ninModification::with(['user', 'transactions']); // Load related data

        if ($request->filled('search')) { // Check if search input is provided
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%") // Search in Reference No.
                    ->orWhere('nin_number', 'like', "%{$searchTerm}%") // Search in BMS ID
                    ->orWhere('status', 'like', "%{$searchTerm}%") // Search in Status
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) { // Search in User fields
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Check if date_from and date_to are provided and filter accordingly
        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom); // Adjust 'created_at' to your date field
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo); // Adjust 'created_at' to your date field
        }

        $nin_services = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'pending' THEN 1
                    WHEN status = 'processing' THEN 2
                    ELSE 3
                END
            ") // Prioritize 'pending' first, then 'processing', and others last
            ->orderByDesc('id') // Sort by latest record within the same priority
            ->paginate(10);

        $request_type = 'mod-services';

        return view('admin.mod-services-list', compact(
            'pending',
            'resolved',
            'rejected',
            'queried',
            'total_request',
            'nin_services',
            'request_type'
        ));
    }

    public function showModRequests($request_id, $type, $requests = null)
    {

        switch ($type) {
            case 'bvn-enrollment':

                break;
            case 'bvn-modification':
                $requests = NinModification::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';

                break;
            case 'upgrade':

                break;

            case 'nin-services':

                break;

            case 'vnin-to-nibss':

                break;

            default:
                $requests = NinModification::with(['user', 'transactions'])->findOrFail($request_id);
                $request_type = 'nin-services';
        }

        if (strtolower($requests->status) == 'rejected') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'admin.view-mod-request',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateModRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        $requestDetails = ninModification::findOrFail($id);
        $route = 'admin.mod.services.list';
        $status = $request->status;

        if ($request->hasFile('document')) {
            $doc = $request->file('document');
            $requestDetails->document = $doc->store('docs', 'public');
        }

        $requestDetails->status = $status;
        $requestDetails->reason = $request->comment;

        if ($request->status === 'rejected') {

            $requestDetails->refunded_at = Carbon::now();

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'Modification Service Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }

    public function ninDelink(Request $request)
    {

        $services = Service::where('type', 'nin_services_delink')
            ->where('status', 'enabled')->get();

        $query = NinValidation::where('user_id', auth()->id())->where('tag', 'DELINK');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nin_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $ninServices = $query->orderBy('id', 'desc')->paginate(10);

        // ✅ Status counts
        $statusCounts = NinValidation::selectRaw('status, COUNT(*) as count')
            ->where('user_id', auth()->user()->id)
            ->where('tag', 'DELINK')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAll = NinValidation::where('user_id', auth()->user()->id)
            ->where('tag', 'DELINK')
            ->count();

        $totalPending = $statusCounts['Pending'] ?? 0;
        $totalFailed = $statusCounts['Failed'] ?? 0;
        $totalInProgress = $statusCounts['In-Progress'] ?? 0;
        $totalSuccessful = $statusCounts['Successful'] ?? 0;

        return view('nin-mod-delink-services', compact(
            'services',
            'ninServices',
            'totalAll',
            'totalPending',
            'totalFailed',
            'totalInProgress',
            'totalSuccessful'
        ));
    }

    public function requestNinServiceDelink(Request $request)
    {
        $rules = [
            'service' => ['required', 'exists:services,service_code'],
        ];

        switch ($request->input('service')) {

            case '131':
            case '132':
            case '133':
                // NIN only
                $rules += [
                    'nin' => ['required', 'digits:11'],
                    'email' => ['required', 'email'],
                ];
                break;
        }

        $request->validate($rules);

        // NIN Services Fee
        $ServiceFee = 0;

        $Service = Service::where('service_code', $request->input('service'))
            ->where('status', 'enabled')
            ->first();

        if (! $Service) {
            return redirect()->back()->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $Service->amount;
        $serviceType = 'Self Service '.$Service->name;
        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            try {

                // $url = env('BASE_API_URL_s8v').'/api/delink';
                // $token = env('API_TOKEN_s8v');

                // $data = ['nin' => $request->input('nin'), 'token' => $token];

                // $headers = [
                //     'Accept: application/json, text/plain, */*',
                //     'Content-Type: application/json',
                // ];

                // // Initialize cURL
                // $ch = curl_init();

                // // Set cURL options
                // curl_setopt($ch, CURLOPT_URL, $url);
                // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                // curl_setopt($ch, CURLOPT_POST, true);
                // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // // Execute request
                // $response = curl_exec($ch);

                // // Check for cURL errors
                // if (curl_errno($ch)) {
                //     throw new \Exception('cURL Error: '.curl_error($ch));
                // }

                // // Close cURL session
                // curl_close($ch);

                // $response = json_decode($response, true);

                // Log::info('Message', $response);

                // if (isset($response['status']) && $response['status'] === 'New') {
                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, $serviceType, $serviceDesc, 'Wallet', 'Approved');

                    $trx_id = $transaction->id;

                    NinValidation::create([
                        'user_id' => $this->loginId,
                        'tnx_id' => $trx_id,
                        'refno' => $transaction->referenceId,
                        'nin_number' => $request->nin,
                        'email'=> $request->email,
                        'description' => $serviceType,
                        'tag' => 'DELINK',
                    ]);

                    return redirect()->back()->with('success', 'Self Service Delink request has been submitted , kindly check the status within 5 working days');
                // } else {
                //     return redirect()->back()->with('error', 'Self Service Delink Request was not successfully');
                // }
            } catch (\Exception $e) {

                redirect()->back()->with('error', 'An error occurred while making the API request');
            }
        }
    }

    public function editNinModification($id)
    {
        $modRequest = NinModification::where('user_id', $this->loginId)->findOrFail($id);

        if ($modRequest->status !== 'query') {
            return redirect()->route('user.nin.mod')->with('error', 'Only queried requests can be edited.');
        }

        $services = Service::where('type', 'nin_mod')->get();

        return view('edit-nin-modification', compact('modRequest', 'services'));
    }

    public function updateNinModification(Request $request, $id)
    {
        $modRequest = NinModification::where('user_id', $this->loginId)->findOrFail($id);

        if ($modRequest->status !== 'query') {
            return redirect()->route('user.nin.mod')->with('error', 'Only queried requests can be edited.');
        }

        $request->validate([
            'nin' => 'required|digits:11',
            'firstname' => 'nullable|string',
            'middlename' => 'nullable|string',
            'surname' => 'nullable|string',
            'dob' => 'nullable|date',
            'phone' => 'nullable|digits_between:10,15',
            'address' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'affidavit' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:2048',
            'full_address' => 'nullable|string',
            'origin_address' => 'nullable|string',
            'state' => 'nullable|string',
            'lga' => 'nullable|string',
            'education_qualification' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'father_full_name' => 'nullable|string',
            'father_state_of_origin' => 'nullable|string',
            'father_lga_of_origin' => 'nullable|string',
            'mother_full_name' => 'nullable|string',
            'mother_state_of_origin' => 'nullable|string',
            'mother_lga_of_origin' => 'nullable|string',
            'mother_maiden_name' => 'nullable|string',
        ]);

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $modRequest->photo = $image->store('photos', 'public');
        }

        if ($request->hasFile('affidavit')) {
            $doc = $request->file('affidavit');
            $modRequest->affidavit = $doc->store('affidavits', 'public');
        }

        $modRequest->update([
            'nin_number' => $request->nin,
            'address' => $request->address,
            'surname' => strtoupper($request->surname),
            'middle_name' => strtoupper($request->middlename),
            'first_name' => strtoupper($request->firstname),
            'phone_number' => $request->phone,
            'dob' => $request->dob,
            'full_address' => $request->full_address,
            'origin_address' => $request->origin_address,
            'state' => $request->state,
            'lga' => $request->lga,
            'education_qualification' => strtoupper($request->education_qualification),
            'marital_status' => strtoupper($request->marital_status),
            'father_full_name' => strtoupper($request->father_full_name),
            'father_state_of_origin' => strtoupper($request->father_state_of_origin),
            'father_lga_of_origin' => strtoupper($request->father_lga_of_origin),
            'mother_full_name' => strtoupper($request->mother_full_name),
            'mother_state_of_origin' => strtoupper($request->mother_state_of_origin),
            'mother_lga_of_origin' => strtoupper($request->mother_lga_of_origin),
            'mother_maiden_name' => strtoupper($request->mother_maiden_name),
            'status' => 'pending', // Reset status to pending after update
        ]);

        return redirect()->route('user.nin.mod')->with('success', 'NIN Modification Request updated and resubmitted successfully.');
    }
}
