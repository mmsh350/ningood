<?php

namespace App\Http\Controllers;

use App\Models\IpeRequest;
use App\Models\NinValidation;
use App\Models\Service;
use App\Models\UserServicePrice;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class APIController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function ipeRequest(Request $request)
    {
        $validated = $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        $user = $request->user();

        $service = Service::where('service_code', '112')
            ->where('status', 'enabled')
            ->first();

        if (! $service) {
            return response()->json([
                'status' => false,
                'code' => 'SERVICE_DISABLED',
                'message' => 'Service is currently unavailable',
            ], 403);
        }

        $now = Carbon::now();

        $userPrice = UserServicePrice::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->latest()
            ->first();

        $serviceFee = $userPrice
            ? $userPrice->custom_price
            : $service->amount;

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (! $wallet || $wallet->balance < $serviceFee) {
            return response()->json([
                'status' => false,
                'code' => 'INSUFFICIENT_BALANCE',
                'message' => 'Wallet balance is insufficient',
                'data' => [
                    'wallet_balance' => $wallet?->balance ?? 0,
                    'required_fee' => $serviceFee,
                ],
            ], 422);
        }

        try {

            $wallet->decrement('balance', $serviceFee);


            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($serviceFee, 2);

           $trx = $this->transactionService->createTransaction($user->id, $serviceFee, 'IPE Request', $serviceDesc, 'Wallet', 'Approved');

             $this->processResponseDataIpe(
                $user->id,
                $trx->id,
                $validated['trackingId']
            );

            return response()->json([
                'status' => true,
                'code' => 'IPE_SUBMITTED',
                'message' => 'IPE request submitted successfully',
                'data' => [
                    'tracking_id' => $validated['trackingId'],
                    'service_fee' => $serviceFee,
                    'balance_left' => $wallet->fresh()->balance,
                    'pricing_type' => $userPrice ? 'custom' : 'default',
                ],
            ], 200);

        } catch (\Throwable $e) {

            Log::error('IPE Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'code' => 'SERVER_ERROR',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    public function processResponseDataIpe($userId, $tnx_id,$trackingNo)
    {
        try {
            IpeRequest::create([
                'user_id' => $userId,
                'trackingId' => $trackingNo,
                'tnx_id' => $tnx_id,
            ]);
        } catch (\Exception $e) {

            Log::error('Request creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create Ipe Request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ipeRequestStatus(Request $request)
    {
        $validated = $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        $user = $request->user();

        $ipe = IpeRequest::where('trackingId', $validated['trackingId'])
            ->where('user_id', $user->id)
            ->first();

        if (! $ipe) {
            return response()->json([
                'status' => false,
                'code' => 'NOT_FOUND',
                'message' => 'Tracking ID not found',
            ], 404);
        }

        if ($ipe->status === 'successful') {
            return response()->json([
                'status' => true,
                'code' => 'SUCCESSFUL',
                'message' => 'IPE request completed successfully',
                'data' => [
                    'tracking_id' => $ipe->trackingId,
                    'reply' => $ipe->reply,
                ],
            ], 200);
        }

        if ($ipe->status === 'failed') {

            // if (is_null($ipe->refunded_at)) {

            //     $service = Service::where('service_code', '112')
            //         ->where('status', 'enabled')
            //         ->first();

            //     if (! $service) {
            //         return response()->json([
            //             'status' => false,
            //             'code' => 'SERVICE_DISABLED',
            //             'message' => 'Refund service unavailable',
            //         ], 403);
            //     }

            //     $now = Carbon::now();

            //     $userPrice = UserServicePrice::where('user_id', $user->id)
            //         ->where('service_id', $service->id)
            //         ->where(function ($q) use ($now) {
            //             $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            //         })
            //         ->where(function ($q) use ($now) {
            //             $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            //         })
            //         ->latest()
            //         ->first();

            //     $refundAmount = $userPrice
            //         ? $userPrice->custom_price
            //         : $service->amount;

            //     DB::transaction(function () use ($user, $ipe, $refundAmount) {

            //         Wallet::where('user_id', $user->id)
            //             ->increment('balance', $refundAmount);

            //         $ipe->update([
            //             'refunded_at' => now(),
            //         ]);

            //         $this->transactionService->createTransaction(
            //             $user->id,
            //             $refundAmount,
            //             'IPE Refund',
            //             "IPE refund for Tracking ID: {$ipe->trackingId}",
            //             'Wallet',
            //             'Approved'
            //         );
            //     });
            // }

            return response()->json([
                'status' => false,
                'code' => 'FAILED',
                'message' => 'IPE request failed',
                'data' => [
                    'tracking_id' => $ipe->trackingId,
                    'reply' => $ipe->reply,
                ],
            ], 200);
        }

        return response()->json([
            'status' => true,
            'code' => 'PENDING',
            'message' => 'IPE request is still being processed',
            'data' => [
                'tracking_id' => $ipe->trackingId,
                'current_status' => $ipe->status,
            ],
        ], 200);
    }

    public function validationRequest(Request $request)
    {
        // $rules = [
        //     'service_code' => [
        //         'required',
        //         Rule::in($allowedServiceCodes),
        //         Rule::exists('services', 'service_code')
        //             ->where('status', 'enabled'),
        //     ],
        //     'nin' => [
        //         Rule::requiredIf(in_array($request->service_code, $allowedServiceCodes)),
        //         'digits:11',
        //     ],
        // ];

        $allowedServiceCodes = ['113'];

        $rules = [
            'nin'     => 'required|digits:11',
            'message' => 'nullable|string|max:50',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {

            $availableServices = \App\Models\Service::whereIn('service_code', $allowedServiceCodes)
                ->where('status', 'enabled')
                ->select('service_code', 'name')
                ->get();

            return response()->json([
                'status' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid request parameters',
                'data' => [
                    'errors' => $validator->errors(),
                    // 'available_services' => $availableServices,
                ],
            ], 422);
        }
        $user = $request->user();

        $service = Service::where('service_code', $allowedServiceCodes)
            ->where('status', 'enabled')
            ->first();

        if (! $service) {
            return response()->json([
                'status' => false,
                'code' => 'SERVICE_DISABLED',
                'message' => 'Service is currently unavailable',
            ], 403);
        }

        $now = Carbon::now();

        $userPrice = UserServicePrice::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->latest()
            ->first();

        $serviceFee = $userPrice
            ? $userPrice->custom_price
            : $service->amount;

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (! $wallet) {
            return response()->json([
                'status' => false,
                'code' => 'WALLET_NOT_FOUND',
                'message' => 'User wallet not found',
            ], 404);
        }

        if ($wallet->balance < $serviceFee) {
            return response()->json([
                'status' => false,
                'code' => 'INSUFFICIENT_BALANCE',
                'message' => 'Wallet balance is insufficient',
                'data' => [
                    'wallet_balance' => $wallet?->balance ?? 0,
                    'required_fee' => $serviceFee,
                ],
            ], 400);
        }

        DB::beginTransaction();

        try {

            $wallet->decrement('balance', $serviceFee);

            $transaction = $this->transactionService->createTransaction(
                $user->id,
                $serviceFee,
                'NIN Validation Request ('.$service->name.')',
                'Wallet debited for NIN validation request',
                'Wallet',
                'Approved'
            );

            $ninRequest = NinValidation::create([
                'user_id' => $user->id,
                'tnx_id' => $transaction->id,
                'refno' => $transaction->referenceId,
                'nin_number' => $request->nin,
                'description' => $request->message ?: $service->name,
                'status' => 'Pending',
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'code' => 'REQUEST_SUBMITTED',
                'message' => 'NIN validation request submitted successfully',
                'data' => [
                    'service' => $service->name,
                    'service_fee' => $serviceFee,
                    'balance_left' => $wallet->fresh()->balance,
                    'pricing_type' => $userPrice ? 'custom' : 'default',
                ],
            ], 200);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'code' => 'SERVER_ERROR',
                'message' => 'Unable to process NIN validation request',
            ], 500);
        }
    }

    public function ValidationRequestStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nin' => ['required', 'digits:11'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 'VALIDATION_ERROR',
                'message' => 'Invalid request parameters',
                'data' => [
                    'errors' => $validator->errors(),
                ],
            ], 422);
        }

        $user = $request->user();

        $ninRequest = NinValidation::where('nin_number', $request->nin)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (! $ninRequest) {
            return response()->json([
                'status' => false,
                'code' => 'NOT_FOUND',
                'message' => 'NIN validation request not found',
            ], 404);
        }

        if ($ninRequest->status === 'Successful') {
            return response()->json([
                'status' => true,
                'code' => 'SUCCESSFUL',
                'message' => 'NIN validation completed successfully',
                'data' => [
                    'nin' => $ninRequest->nin_number,
                    'reference' => $ninRequest->refno,
                    'reply' => $ninRequest->reason,
                ],
            ], 200);
        }

        if ($ninRequest->status === 'Failed') {
            return response()->json([
                'status' => false,
                'code' => 'FAILED',
                'message' => 'NIN validation failed',
                'data' => [
                    'nin' => $ninRequest->nin_number,
                    'reference' => $ninRequest->refno,
                    'reply' => $ninRequest->reason,
                ],
            ], 200);
        }

        if (in_array($ninRequest->status, ['Pending', 'In-Progress'])) {
            return response()->json([
                'status' => true,
                'code' => 'PENDING',
                'message' => 'NIN validation is still being processed',
                'data' => [
                    'nin' => $ninRequest->nin_number,
                    'reference' => $ninRequest->refno,
                    'current_status' => $ninRequest->status,
                ],
            ], 200);
        }

        return response()->json([
            'status' => false,
            'code' => 'UNKNOWN_STATUS',
            'message' => 'Unknown NIN validation status',
            'data' => [
                'current_status' => $ninRequest->status,
            ],
        ], 500);
    }
}
