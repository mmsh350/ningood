<?php

namespace App\Console\Commands;

use App\Models\ModificationRequest;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckBvnRequestStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-bvn-request-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BVN Modification Status Check';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('🔍 Starting BVN modification status check...');

        $pendingRequests = ModificationRequest::whereIn('status', ['pending', 'processing'])->get();

        foreach ($pendingRequests as $request) {
            $refno = $request->refno;
            $userId = $request->user_id;

            try {
                $url = env('BASE_URL_VERIFY_USER').'api/v1/bvn-modifications/status/'.$refno;
                $token = env('VERIFY_USER_TOKEN');

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ])
                    ->timeout(30)
                    ->get($url);

                if (! $response->successful()) {
                    Log::error("❌ API request failed for refno $refno", [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    continue;
                }

                $data = $response->json();

                // Check if API response is successful and has data
                if (! isset($data['success']) || ! $data['success'] || ! isset($data['data'])) {
                    Log::warning("⚠️ API returned unsuccessful response for refno $refno", $data);

                    continue;
                }

                Log::info("✅ API response for refno $refno", $data);

                $apiStatus = $data['data']['status'] ?? null;
                $reason = $data['data']['reason'] ?? null;

                Log::info("✅ Status for $refno: ".$apiStatus);

                // Update the request with current status and reason
                $updateData = [
                    'reason' => $reason,
                    'status' => $apiStatus,
                    'updated_at' => Carbon::now(),
                ];

                // Handle different statuses
                if (in_array($apiStatus, ['resolved', 'processing', 'rejected'])) {
                    ModificationRequest::where('refno', $refno)
                        ->where('user_id', $userId)
                        ->update($updateData);

                    Log::info("✅ Updated request $refno to status: $apiStatus");

                    // Handle rejected requests and refund
                    if ($apiStatus === 'rejected') {
                        $this->handleRejectedRequest($request, $refno, $userId, $reason);
                    }
                } else {
                    Log::warning("⚠️ Unknown status '$apiStatus' for refno $refno");
                }
            } catch (\Exception $e) {
                Log::error("❌ Error checking BVN status for refno $refno: ".$e->getMessage());
            }
        }

        Log::info('✅ Done checking BVN modification requests.');
    }

    /**
     * Handle rejected request and process refund
     */
    private function handleRejectedRequest($request, $refno, $userId, $reason)
    {
        Log::info("🔄 Processing refund for rejected request: $refno");

        $wallet = Wallet::where('user_id', $userId)->first();

        if (! $wallet) {
            Log::warning("⚠️ Wallet not found for user ID $userId");

            return;
        }

        // Check if already refunded
        $refundCheck = ModificationRequest::where('refno', $refno)
            ->where('user_id', $userId)
            ->whereNotNull('refunded_at')
            ->first();

        if ($refundCheck) {
            Log::info("✅ Refund already processed for refno $refno");

            return;
        }

        // Process refund
        try {
            $refundAmount = $request->total_price ?? 0;

            if ($refundAmount <= 0) {
                Log::warning("⚠️ Invalid refund amount for refno $refno: $refundAmount");

                return;
            }

            // Update wallet balance
            $wallet->update([
                'balance' => $wallet->balance + $refundAmount,
            ]);

            // Update request with refund details
            ModificationRequest::where('refno', $refno)
                ->where('user_id', $userId)
                ->update([
                    'refunded_at' => Carbon::now(),
                    'reason' => $reason,
                ]);

            // Create transaction record
            app(TransactionService::class)->createTransaction(
                $userId,
                $refundAmount,
                'BVN Modification Refund',
                "BVN Modification Refund - Reason: {$reason} - Reference ID: {$refno}",
                'Wallet',
                'Approved'
            );

            Log::info("✅ Successfully processed refund of $refundAmount for refno $refno");
        } catch (\Exception $e) {
            Log::error("❌ Error processing refund for refno $refno: ".$e->getMessage());
        }
    }
}
