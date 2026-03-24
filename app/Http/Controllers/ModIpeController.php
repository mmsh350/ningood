<?php

namespace App\Http\Controllers;

use App\Models\ModIpeClearance;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ModIpeController extends Controller
{
    protected $transactionService;

    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->user()->id;
    }

    public function ninModIpe(Request $request)
    {

        $services = Service::where('service_code', '135')
            ->where('status', 'enabled')->get();

        $query = ModIpeClearance::where('user_id', auth()->id());

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nin_number', 'like', "%{$search}%")
                    ->orWhere('trackingId', 'like', "%{$search}%")
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
        $statusCounts = ModIpeClearance::selectRaw('status, COUNT(*) as count')
            ->where('user_id', auth()->user()->id)
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAll = ModIpeClearance::where('user_id', auth()->user()->id)
            ->count();

        $totalPending = $statusCounts['pending'] ?? 0;
        $totalFailed = $statusCounts['rejected'] ?? 0;
        $totalInProgress = $statusCounts['processing'] ?? 0;
        $totalSuccessful = $statusCounts['resolved'] ?? 0;

        return view('nin-mod-ipe-services', compact(
            'services',
            'ninServices',
            'totalAll',
            'totalPending',
            'totalFailed',
            'totalInProgress',
            'totalSuccessful'
        ));
    }

    public function requestNinServiceIPE(Request $request)
    {
        $rules = [
            'service' => ['required', 'exists:services,service_code'],
        ];

        switch ($request->input('service')) {

            case '135':
                // NIN only
                $rules += [
                    'nin' => ['required', 'digits:11'],
                    'tracking_no' => ['required', 'alpha_num', 'size:15'],
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
        $serviceType = $Service->name;
        // Check if wallet is funded
        $wallet = Wallet::where('user_id', auth()->id())->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        $pendingRequest = ModIpeClearance::where('tracking_id', $request->tracking_no)
            ->whereIn('status', ['pending'])
            ->exists();

        if ($pendingRequest) {
            return redirect()->back()->with('error', 'Sorry, you already have a pending request with that tracking ID! !');
        }

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            try {

                $balance = $wallet->balance - $ServiceFee;

                Wallet::where('user_id', auth()->id())
                    ->update(['balance' => $balance]);

                $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, $serviceType.' Request', $serviceDesc, 'Wallet', 'Approved');

                $trx_id = $transaction->id;

                try {
                    ModIpeClearance::create([
                        'user_id' => $this->loginId,
                        'tnx_id' => $trx_id,
                        'refno' => $transaction->referenceId,
                        'nin_number' => $request->nin,
                        'tracking_id' => $request->tracking_no,
                    ]);
                } catch (\Exception $e) {
                    dd($e->getMessage()); // OR log it:
                    Log::error('SQL Error on ModIpeClearance create', ['message' => $e->getMessage()]);
                }

                return redirect()->back()->with('success', 'Modification IPE request has been submitted , kindly check status after 24 working hours');
            } catch (\Exception $e) {

                return redirect()->back()->with('error', 'An error occurred while saving your request');
            }
        }
    }

    public function ninServicesList(Request $request)
    {

        // Services
        $pending = ModIpeClearance::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = ModIpeClearance::where('status', 'resolved')
            ->count();

        $rejected = ModIpeClearance::where('status', 'rejected')
            ->count();

        $total_request = ModIpeClearance::count();

        $query = ModIpeClearance::with(['user', 'transactions']); // Load related data

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

        $request_type = 'nin-mod-ipe';

        return view('admin.mod-ipe-list', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'nin_services',
            'request_type'
        ));
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {
        $request->validate([
            'status' => 'required|string',
            'comment' => 'required|string',
        ]);

        $requestDetails = ModIpeClearance::findOrFail($id);
        $route = 'admin.modipe.index';
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

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'NIN Service Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }
}
