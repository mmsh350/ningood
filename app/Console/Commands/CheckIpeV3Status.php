<?php

namespace App\Console\Commands;

use App\Models\IpeRequest;
use App\Models\Wallet;
use App\Models\Service;
use App\Services\TransactionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckIpeV3Status extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-ipe-v3-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check IPE V3 status and update database with automation and refunds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        Log::info('🔍 Checking pending IPE V3 requests...');

        $requests = IpeRequest::where('tag', 'IPE_V3')
            ->whereIn('status', ['pending', 'processing'])
            ->oldest('updated_at')
            ->limit(100)
            ->get();

        if ($requests->isEmpty()) {
            Log::info('✅ No pending IPE V3 requests found.');
            return;
        }

        foreach ($requests as $request) {
            try {
                $request->touch();
                $url = env('BASE_URL_VERIFY_USER2') . 'api/v1/ipe-status';
                $token = env('VERIFY_USER_TOKEN2');
                $data = ['trackingId' => $request->trackingId];

                Log::info("Checking status for IPE V3: {$request->trackingId}");

                $response = Http::withToken($token)
                    ->timeout(30)
                    ->post($url, $data);

                if (!$response->successful()) {
                    Log::error("❌ API request failed for IPE V3 trackingId {$request->trackingId}", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    continue;
                }

                $responseData = $response->json();

                if (isset($responseData['status']) && $responseData['status'] === true) {
                    $apiData = $responseData['data'] ?? [];
                    $statusCode = (string)($apiData['status_code'] ?? '100');
                    $reply = $apiData['reply'] ?? $responseData['message'] ?? 'Status updated';

                    Log::info("✅ Status for {$request->trackingId}: $statusCode");

                    if ($statusCode == '200') {
                        $request->update([
                            'status' => 'successful',
                            'reply' => $reply,
                            'resp_code' => '200'
                        ]);
                        Log::info("IPE V3 {$request->trackingId} Successful");
                    } elseif ($statusCode == '400') {
                        $this->handleRefund($request, $reply);
                    } elseif ($statusCode == '101') {
                        $request->update([
                            'status' => 'processing',
                            'resp_code' => '101'
                        ]);
                        Log::info("IPE V3 {$request->trackingId} Processing");
                    }
                } else {
                    Log::warning("⚠️ API returned unsuccessful status for {$request->trackingId}: " . ($responseData['message'] ?? 'No message'));
                }

            } catch (\Exception $e) {
                Log::error("❌ Error checking IPE V3 status for {$request->trackingId}: " . $e->getMessage());
            }
        }

        Log::info('✅ Finished checking IPE V3 requests.');
    }

    private function handleRefund($request, $reply)
    {
        Log::info("🔄 Processing refund for rejected IPE V3: {$request->trackingId}");

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
                'reply' => $reply . ' (Refunded)',
                'refunded_at' => Carbon::now(),
                'resp_code' => '400'
            ]);

            app(TransactionService::class)->createTransaction(
                $request->user_id,
                $service->amount,
                'IPE V3 Refund',
                "Refund for Tracking ID: {$request->trackingId} Reason: " . $reply,
                'Wallet',
                'Approved'
            );

            Log::info("✅ Refund of {$service->amount} completed for user {$request->user_id}");
        } else {
            Log::warning("⚠️ Wallet not found for user {$request->user_id}");
        }
    }
}
