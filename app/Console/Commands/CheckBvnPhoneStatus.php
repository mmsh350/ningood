<?php

namespace App\Console\Commands;

use App\Models\BvnPhoneSearch;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckBvnPhoneStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-bvn-phone-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check BVN phone number request status and update database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('🔍 Starting BVN Phone Request status check...');

        $requests = BvnPhoneSearch::whereIn('status', ['pending', 'processing'])->get();

        if ($requests->isEmpty()) {
            Log::info('✅ No pending BVN phone requests found.');

            return;
        }

        $statusMap = [
            100 => 'pending',
            101 => 'processing',
            200 => 'resolved',
            400 => 'rejected',
        ];

        foreach ($requests as $request) {
            $refno = $request->refno;
            $userId = $request->user_id;

            try {
                $url = env('BASE_URL_VERIFY_USER2').'api/v1/bvn/status/'.$refno;
                $token = env('VERIFY_USER_TOKEN2');

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
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

                if (! isset($data['success']) || ! $data['success']) {
                    $reason = $data['message'] ?? 'BVN search record not found.';
                    Log::warning("⚠️ API unsuccessful for refno $refno: $reason");

                    continue;
                }

                $responseData = $data['data'] ?? [];
                $statusCode = (int) ($responseData['status_code'] ?? 100);
                $statusText = $statusMap[$statusCode];
                $reason = $responseData['bvn'] ?? null;

                Log::info("✅ Refno $refno: Status -> $statusText | BVN -> $reason");

                $request->update([
                    'status' => $statusText,
                    'reason' => $reason,
                    'name' => 'API',
                    'updated_at' => Carbon::now(),
                ]);

                if ($statusCode === 400) {
                    $this->handleRefund($request, $userId, $refno, $reason);
                }
            } catch (\Exception $e) {
                Log::error("❌ Error checking BVN phone status for refno $refno: ".$e->getMessage());
            }
        }

        Log::info('✅ Completed BVN Phone Request status check.');
    }

    private function handleRefund($request, $userId, $refno, $reason)
    {
        Log::info("🔄 Processing refund for failed refno $refno");

        $wallet = Wallet::where('user_id', $userId)->first();

        if (! $wallet) {
            Log::warning("⚠️ Wallet not found for user ID $userId");

            return;
        }

        if ($request->refunded_at) {
            Log::info("✅ Refund already processed for refno $refno");

            return;
        }

        $refundAmount = $request->transactions->amount ?? 0;

        if ($refundAmount <= 0) {
            Log::warning("⚠️ Invalid refund amount for refno $refno");

            return;
        }

        try {

            $wallet->update([
                'balance' => $wallet->balance + $refundAmount,
            ]);

            $request->update([
                'refunded_at' => Carbon::now(),
                'reason' => $reason,
            ]);

            app(TransactionService::class)->createTransaction(
                $userId,
                $refundAmount,
                'BVN Phone Request Refund',
                "Refund for BVN phone request failed - Ref: {$refno} | Reason: {$reason}",
                'Wallet',
                'Approved'
            );

            Log::info("✅ Refund processed for refno $refno amount ₦{$refundAmount}");
        } catch (\Exception $e) {
            Log::error("❌ Error refunding refno $refno: ".$e->getMessage());
        }
    }
}
