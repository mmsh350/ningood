<?php

namespace App\Http\Controllers;

use App\Exports\IpeTemplateExport;
use App\Models\IpeRequest;
use App\Models\Service;
use App\Models\UserServicePrice;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class IpeController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function ipeIndex(Request $request)
    {

        $counts = IpeRequest::selectRaw("
        COUNT(*) as total_request,
        SUM(CASE WHEN resp_code IN ('100','101') THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN resp_code = '200' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN resp_code = '400' THEN 1 ELSE 0 END) as rejected")
            ->first();

        $pending = $counts->pending ?? 0;
        $resolved = $counts->resolved ?? 0;
        $rejected = $counts->rejected ?? 0;
        $total_request = $counts->total_request ?? 0;

        // Filters
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $ipeRequestsQuery = IpeRequest::whereNull('tag');

        if ($search) {
            $ipeRequestsQuery->where(function ($query) use ($search) {
                $query->where('trackingid', 'like', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $ipeRequestsQuery->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $ipeRequestsQuery->whereDate('created_at', '<=', $dateTo);
        }

        $ipeRequestsQuery->orderByRaw("
        CASE resp_code
            WHEN '100' THEN 0
            WHEN '101' THEN 1
            WHEN '200' THEN 2
            WHEN '400' THEN 3
            ELSE 4
        END")->orderByDesc('created_at');

        $ipeRequests = $ipeRequestsQuery->select(
            'id',
            'user_id',
            'trackingId',
            'resp_code',
            'reply',
            'status',
            'created_at',
            'updated_at'
        )->paginate($perPage)->withQueryString();

         $refund_count = IpeRequest::where('resp_code', '400')
            ->whereNull('refunded_at')
            ->count();

        return view('admin.ipe-index', compact(
            'pending',
            'resolved',
            'rejected',
            'total_request',
            'ipeRequests',
            'refund_count'
        ));
    }

    public function downloadTemplateIPE()
    {
        $records = IpeRequest::whereIn('resp_code', ['100', '101'])
            ->whereNull('tag')
            ->select('id', 'trackingId', 'resp_code', 'reply')
            ->get();

        if ($records->isEmpty()) {
            return back()->with('error', 'No pending records to export.');
        }

        $ids = $records->pluck('id')->toArray();

        IpeRequest::whereIn('id', $ids)
            ->update(['resp_code' => '101']);

        return Excel::download(
            new IpeTemplateExport($records),
            'ipe_requests_pending_'.now()->format('Y_m_d_His').'.xlsx'
        );
    }

    public function uploadExcelIPE(Request $request)
    {
        try {
            // Validate uploaded file
            $validator = Validator::make($request->all(), [
                'excel_file' => 'required|file|mimes:xlsx,xls',
            ]);

            if ($validator->fails()) {
                return back()->with('error', 'The file field is required and must be an Excel file.');
            }

            $data = Excel::toArray([], $request->file('excel_file'))[0];

            if (count($data) < 2) {
                return back()->with('error', 'The uploaded file is empty or has no valid data.');
            }

            $header = array_map('strtolower', $data[0]);

            if (! in_array('tracking_id', $header) || ! in_array('resp_code', $header) || ! in_array('reply', $header)) {
                return back()->with('error', 'Invalid file format. Required headers: tracking_id, resp_code, reply.');
            }

            $successCount = 0;
            $failedRows = [];

            // Process each row
            for ($i = 1; $i < count($data); $i++) {
                $row = array_combine($header, $data[$i]);

                $trackingId = trim($row['tracking_id'] ?? '');
                $respCode = trim((string) ($row['resp_code'] ?? ''));
                $reply = trim($row['reply'] ?? '');

                $rowNumber = $i + 1;

                // Validation
                if (! $trackingId || ! $respCode || ! $reply) {
                    $failedRows[] = "Row $rowNumber: Missing tracking_id, resp_code or reply.";

                    continue;
                }

                if (! in_array($respCode, ['200', '400'])) {
                    $failedRows[] = "Row $rowNumber: Invalid resp_code '$respCode'. Only 200 and 400 are allowed.";

                    continue;
                }

                $respCode == '200' ? $st = 'successful' : $st = 'failed';
                // Perform update
                $updated = IpeRequest::where('trackingId', $trackingId)
                    ->whereNull('tag')
                    ->where('resp_code', '101')
                    ->update([
                        'resp_code' => $respCode,
                        'reply' => $reply,
                        'status' => $st,
                        'updated_at' => Carbon::now(),
                    ]);

                if ($updated) {
                    $successCount++;
                } else {
                    $failedRows[] = "Row $rowNumber: Tracking ID '$trackingId' not found in the database.";
                }
            }

            // Prepare response message
            $message = "$successCount rows updated successfully.";
            if (count($failedRows)) {
                $message .= ' Some rows failed: <br><ul>';
                foreach ($failedRows as $error) {
                    $message .= "<li>$error</li>";
                }
                $message .= '</ul>';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Excel upload error: '.$e->getMessage());

            return back()->with('error', 'An error occurred while processing the file: '.$e->getMessage());
        }
    }

    public function refundFailedTransactions()
    {
        $failedRequests = IpeRequest::where('resp_code', '400')
            ->whereNull('refunded_at')
            ->get();

        $refunded = 0;
        foreach ($failedRequests as $request) {

            $success = $this->processRefund($request);

            if ($success) {

                IpeRequest::where('trackingId', $request->tracking_id)
                    ->update([
                        'refunded_at' => Carbon::now(),
                    ]);

                $refunded++;
            }
        }

        return back()->with('success', "Refunded {$refunded} transaction(s).");
    }
    private function processRefund($request): bool
    {
        try {


            $userId = $request->user_id;

        if ($request->status === 'failed') {

            if (is_null($request->refunded_at)) {

                $service = Service::where('service_code', '112')
                    ->where('status', 'enabled')
                    ->first();

                // $now = Carbon::now();

                // $userPrice = UserServicePrice::where('user_id', $userId)
                //     ->where('service_id', $service->id)
                //     ->where(function ($q) use ($now) {
                //         $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
                //     })
                //     ->where(function ($q) use ($now) {
                //         $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
                //     })
                //     ->latest()
                //     ->first();

                // $refundAmount = $userPrice
                //     ? $userPrice->custom_price
                //     : $service->amount;

                 $refundAmount =$request->transactions->amount;

                 log::info($refundAmount);

                DB::transaction(function () use ($userId, $request, $refundAmount) {

                    Wallet::where('user_id', $userId)
                        ->increment('balance', $refundAmount);

                    $request->update([
                        'refunded_at' => now(),
                    ]);

                    $this->transactionService->createTransaction(
                        $userId,
                        $refundAmount,
                        'IPE Refund',
                        "IPE refund for Tracking ID: {$request->trackingId}",
                        'Wallet',
                        'Approved'
                    );
                });
            }


        }

        Log::info("Refund processed for Tracking ID: {$request->trackingId} - USER ID: {$userId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Refund failed for Tracking ID: {$request->trackingId}. Error: ".$e->getMessage());

            return false;
        }
    }
}
