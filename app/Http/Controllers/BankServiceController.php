<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\BankService;
use App\Models\ModificationRequest;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Wallet;
use App\Services\BankDataService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BankServiceController extends Controller
{
    protected $bankDataService;

    protected $transactionService;

    protected $user_id;

    public function __construct(BankDataService $bankDataService, TransactionService $transactionService)
    {
        $this->bankDataService = $bankDataService;
        $this->user_id = auth()->id();
        $this->transactionService = $transactionService;
    }

    public function index(Request $request)
    {
        $userId = auth()->id();

        // Search and status filter inputs
        $search = $request->input('search');
        $status = $request->input('status');

        // Get count per status
        $statusCounts = ModificationRequest::selectRaw('status, COUNT(*) as count')
            ->where('user_id', $userId)
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalPending = $statusCounts['pending'] ?? 0;
        $totalFailed = $statusCounts['rejected'] ?? 0;
        $totalInProgress = $statusCounts['processing'] ?? 0;
        $totalSuccessful = $statusCounts['resolved'] ?? 0;
        $totalQueried = $statusCounts['query'] ?? 0;

        $totalAll = ModificationRequest::where('user_id', $userId)->count();

        // Active Banks
        $banks = Bank::with(['bankServices.service'])
            ->where('is_active', true)
            ->get();

        // Build query with optional filters
        $query = ModificationRequest::with(['bank', 'service'])
            ->where('user_id', $userId);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('refno', 'like', "%{$search}%")
                    ->orWhere('bvn_no', 'like', "%{$search}%")
                    ->orWhere('nin_number', 'like', "%{$search}%");
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        // Paginate and preserve filters
        $modificationRequests = $query->latest()
            ->paginate(10)
            ->appends($request->only(['search', 'status']));

        $consent = SiteSetting::first();

        return view('bvn-modification', compact(
            'banks',
            'modificationRequests',
            'totalAll',
            'totalPending',
            'totalFailed',
            'totalInProgress',
            'totalSuccessful',
            'totalQueried',
            'search',
            'status',
            'consent'
        ));
    }

    public function adminList(Request $request)
    {

        // Search and status filter inputs
        $search = $request->input('search');
        $status = $request->input('status');

        // Get count per status
        $statusCounts = ModificationRequest::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalPending = $statusCounts['pending'] ?? 0;
        $totalFailed = $statusCounts['rejected'] ?? 0;
        $totalInProgress = $statusCounts['processing'] ?? 0;
        $totalSuccessful = $statusCounts['resolved'] ?? 0;
        $totalQueried = $statusCounts['query'] ?? 0;

        $totalAll = ModificationRequest::count();

        // Build query with optional filters
        $query = ModificationRequest::with(['bank', 'service', 'user']);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('refno', 'like', "%{$search}%")
                    ->orWhere('bvn_no', 'like', "%{$search}%")
                    ->orWhere('nin_number', 'like', "%{$search}%");
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        // Paginate and preserve filters
        $modificationRequests = $query->latest()
            ->paginate(10)
            ->appends($request->only(['search', 'status']));

        return view('admin.bvn-modification', compact(
            'modificationRequests',
            'totalAll',
            'totalPending',
            'totalFailed',
            'totalInProgress',
            'totalSuccessful',
            'totalQueried',
            'search',
            'status'
        ));
    }

    public function getServices($bankId)
    {
        $services = BankService::with('service')
            ->where('bank_id', $bankId)
            ->where('is_active', true)
            ->get()
            ->map(function ($bankService) {
                return [
                    'id' => $bankService->service->id,
                    'name' => $bankService->service->name,
                    'code' => $bankService->service->code,
                    'price' => $bankService->price,
                    'commission' => $bankService->commission,
                    'total_price' => $bankService->total_price,
                ];
            });

        return response()->json($services);
    }


    public function storeRequest(Request $request)
    {
        $request->validate([
            'bvn_no' => 'required|string|size:11',
            'nin_number' => 'required|string|size:11',
            'bank_id' => 'required|exists:banks,id',
            'service_id' => 'required|exists:services,id',
            // Current Details
            'current_firstname' => 'nullable|string',
            'current_middlename' => 'nullable|string',
            'current_surname' => 'nullable|string',
            'current_dob' => 'nullable|string',
            'current_phone' => 'nullable|string',
            'current_gender' => 'nullable|string',
            'current_address' => 'nullable|string',
            // New Details
            'new_firstname' => 'required|string',
            'new_middlename' => 'nullable|string',
            'new_surname' => 'required|string',
            'new_dob' => 'required|string',
            'new_phone' => 'required|string',
            'new_gender' => 'required|string',
            'new_address' => 'required|string',
        ]);

        $bankService = BankService::where('bank_id', $request->bank_id)
            ->where('service_id', $request->service_id)
            ->firstOrFail();

        $service = Service::find($request->service_id);
        $totalRequestPrice = $bankService->total_price;

        $wallet = Wallet::where('user_id', $this->user_id)->first();
        $wallet_balance = $wallet->balance;

        if ($wallet_balance < $totalRequestPrice) {
            return redirect()->back()->with('error', 'Insufficient Wallet Balance! Please fund your wallet.');
        }

        $modification_data = [
            'current_data' => [
                'First Name' => $request->current_firstname,
                'Middle Name' => $request->current_middlename,
                'Surname' => $request->current_surname,
                'Date of Birth' => $request->current_dob,
                'Phone Number' => $request->current_phone,
                'Gender' => $request->current_gender,
                'Address' => $request->current_address,
            ],
            'new_data' => [
                'First Name' => $request->new_firstname,
                'Middle Name' => $request->new_middlename,
                'Surname' => $request->new_surname,
                'Date of Birth' => $request->new_dob,
                'Phone Number' => $request->new_phone,
                'Gender' => $request->new_gender,
                'Address' => $request->new_address,
            ],
        ];

        $refno = 'BVN-' . date('YmdHis');

        $wallet->decrement('balance', $totalRequestPrice);

        ModificationRequest::create([
            'user_id' => $this->user_id,
            'refno' => $refno,
            'bvn_no' => $request->bvn_no,
            'nin_number' => $request->nin_number,
            'bank_id' => $request->bank_id,
            'service_id' => $request->service_id,
            'modification_data' => $modification_data,
            'base_price' => $bankService->price,
            'commission' => $bankService->commission ?? 0,
            'total_price' => $totalRequestPrice,
            'status' => 'pending',
        ]);

        $serviceDesc = 'Wallet debited for BVN Modification Request (Service: ' . $service->name . ')';
        $this->transactionService->createTransaction(
            $this->user_id,
            $totalRequestPrice,
            'BVN Modification Request',
            $serviceDesc,
            'Wallet',
            'Approved',
            $refno
        );

        return redirect()->back()->with('success', 'Request Submitted Successfully');
    }

    public function editRequest($id)
    {
        $modRequest = ModificationRequest::where('user_id', auth()->id())
            ->where('status', 'query')
            ->findOrFail($id);

        $banks = Bank::all();
        $bankServices = BankService::with('service')->where('bank_id', $modRequest->bank_id)->get();

        return view('edit-bvn-modification', compact('modRequest', 'banks', 'bankServices'));
    }

    public function updateRequest(Request $request, $id)
    {
        $modRequest = ModificationRequest::where('user_id', auth()->id())
            ->where('status', 'query')
            ->findOrFail($id);

        $request->validate([
            'bvn_no' => 'required|string|size:11',
            'nin_number' => 'required|string|size:11',
            'new_firstname' => 'required|string',
            'new_middlename' => 'nullable|string',
            'new_surname' => 'required|string',
            'new_dob' => 'required|string',
            'new_phone' => 'required|string',
            'new_gender' => 'required|string',
            'new_address' => 'required|string',
        ]);

        $modification_data = [
            'current_data' => [
                'First Name' => $request->current_firstname,
                'Middle Name' => $request->current_middlename,
                'Surname' => $request->current_surname,
                'Date of Birth' => $request->current_dob,
                'Phone Number' => $request->current_phone,
                'Gender' => $request->current_gender,
                'Address' => $request->current_address,
            ],
            'new_data' => [
                'First Name' => $request->new_firstname,
                'Middle Name' => $request->new_middlename,
                'Surname' => $request->new_surname,
                'Date of Birth' => $request->new_dob,
                'Phone Number' => $request->new_phone,
                'Gender' => $request->new_gender,
                'Address' => $request->new_address,
            ],
        ];

        $modRequest->update([
            'bvn_no' => $request->bvn_no,
            'nin_number' => $request->nin_number,
            'modification_data' => $modification_data,
            'status' => 'pending',
        ]);

        return redirect()->route('user.bank-services.index')->with('success', 'Request Updated and Resubmitted Successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $modRequest = ModificationRequest::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,resolved,rejected,query',
            'reason' => 'nullable|string',
            'comment' => 'nullable|string',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $modRequest->total_price,
        ]);

        $oldStatus = $modRequest->status;
        $newStatus = $request->status;

        $modRequest->status = $newStatus;
        $modRequest->reason = $request->comment ?? $request->reason;
        $modRequest->save();

        if ($newStatus === 'rejected' && $oldStatus !== 'rejected') {
            $refundAmount = $request->refund_amount ?? $modRequest->total_price;

            if ($refundAmount > 0) {
                $wallet = Wallet::where('user_id', $modRequest->user_id)->first();
                $wallet->increment('balance', $refundAmount);

                $this->transactionService->createTransaction(
                    $modRequest->user_id,
                    $refundAmount,
                    'BVN Modification Refund',
                    'Refund for rejected BVN modification request: ' . $modRequest->refno,
                    'Wallet',
                    'Approved',
                    'REF-' . $modRequest->refno . date('YmdHis')
                );
            }
        }

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    public function manageBankServices()
    {
        $banks = Bank::all();
        $bankServices = BankService::with(['bank', 'service'])->orderBy('bank_id')->get();
        return view('admin.manage-bank-services', compact('banks', 'bankServices'));
    }

    public function updateBankServicePrice(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'service_id' => 'required|exists:services,id',
            'price' => 'required|numeric|min:0',
            'commission' => 'required|numeric|min:0',
        ]);

        BankService::updateOrCreate(
            ['bank_id' => $request->bank_id, 'service_id' => $request->service_id],
            ['price' => $request->price, 'commission' => $request->commission]
        );

        return redirect()->back()->with('success', 'Bank service price updated successfully.');
    }


}
