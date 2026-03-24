<?php

namespace App\Http\Controllers;

use App\Models\IpeRequest;
use App\Models\Wallet;
use App\Models\Service;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class IpeWebhookController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Handle the IPE V3 Webhook.
     *
     * Expects similar structure to the API status response.
     */
    public function handleWebhook(Request $request)
    {
        Log::info('📦 IPE V3 Webhook received:', $request->all());

        // Extract tracking_id from data object as per sample or fallback to previous aliases
        $trackingId = $request->input('data.tracking_id') ?? $request->input('tracking_id') ?? $request->input('trackingId');

        if (!$trackingId) {
            Log::error('❌ IPE Webhook: No trackingId provided in request.', $request->all());
            return response()->json(['status' => false, 'message' => 'Tracking ID missing'], 400);
        }

        $ipeRequest = IpeRequest::where('trackingId', $trackingId)
            ->where('tag', 'IPE_V3')
            ->latest()
            ->first();

        if (!$ipeRequest) {
            Log::warning("⚠️ IPE Webhook: Tracking ID {$trackingId} not found for tag IPE_V3");
            return response()->json(['status' => false, 'message' => 'Request not found'], 404);
        }

        // Avoid re-processing finished requests
        if ($ipeRequest->status === 'successful' || $ipeRequest->status === 'failed') {
            Log::info("ℹ️ IPE Webhook: Request {$trackingId} already processed (Status: {$ipeRequest->status})");
            return response()->json(['status' => true, 'message' => 'Already processed']);
        }

        // Logic similar to CheckIpeV3Status.php
        $apiData = $request->input('data') ?? $request->all();
        $statusCode = (string)($apiData['status'] ?? $apiData['status_code'] ?? '100');
        $reply = $request->input('message') ?? $apiData['reply'] ?? 'Status updated via webhook';

        Log::info("🔄 Processing IPE Webhook Status for {$trackingId}: $statusCode");

        if ($statusCode == '200') {
            $ipeRequest->update([
                'status' => 'successful',
                'reply' => $reply,
                'resp_code' => '200'
            ]);
            Log::info("✅ IPE V3 {$trackingId} marked as Successful via Webhook");
        } elseif ($statusCode == '400') {
            $this->handleRefund($ipeRequest, $reply);
        } elseif ($statusCode == '101') {
            $ipeRequest->update([
                'status' => 'processing',
                'resp_code' => '101'
            ]);
            Log::info("🕒 IPE V3 {$trackingId} marked as Processing via Webhook");
        } else {
            Log::info("ℹ️ IPE Webhook: Unhandled status code {$statusCode} for {$trackingId}");
        }

        return response()->json(['status' => true, 'message' => 'Webhook processed successfully']);
    }

    private function handleRefund($request, $reply)
    {
        Log::info("🔄 Processing refund for rejected IPE V3 via Webhook: {$request->trackingId}");

        if ($request->refunded_at) {
            Log::info("✅ Already refunded: {$request->trackingId}");
            return;
        }

        $service = Service::where('service_code', '112')->first();
        if (!$service) {
            Log::warning("⚠️ Service 112 not found for refund");
            return;
        }

        $wallet = Wallet::where('user_id', $request->user_id)->first();
        if ($wallet) {
            $wallet->increment('balance', $service->amount);

            $request->update([
                'status' => 'failed',
                'reply' => $reply . ' (Refunded via Webhook)',
                'refunded_at' => Carbon::now(),
                'resp_code' => '400'
            ]);

            $this->transactionService->createTransaction(
                $request->user_id,
                $service->amount,
                'IPE V3 Refund',
                "Refund for Tracking ID: {$request->trackingId} Reason: " . $reply,
                'Wallet',
                'Approved'
            );

            Log::info("✅ Refund of {$service->amount} completed for user {$request->user_id} via Webhook");
        } else {
            Log::warning("⚠️ Wallet not found for user {$request->user_id}");
        }
    }
}
