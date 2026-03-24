<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CheckIpeRequestStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-ipe-request-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ipe Status Check';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Log::info('Checking pending IPE requests...');

        $pendingRequests = \App\Models\IpeRequest::where('status', 'pending')->get();

        foreach ($pendingRequests as $request) {
            try {
                $trackingId = $request->trackingId;
                $userId = $request->user_id;

                $url = env('BASE_API_URL_s8v').'/api/clearance/status';
                $token = env('API_TOKEN_s8v');

                $data = ['tracking_id' => $trackingId, 'token' => $token];

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                curl_close($ch);

                $response = json_decode($response, true);

                Log::info('Response', $response);

                if (isset($response['status']) && $response['status'] === 'Successful') {
                    \App\Models\IpeRequest::where('trackingId', $trackingId)
                        ->where('user_id', $userId)
                        ->update([
                            'reply' => ($response['reply'] ?? 'N/A').'<br>'.
                                ($response['nin'] ?? 'N/A').'<br>'.
                                'Name: '.($response['name'] ?? 'N/A').'<br>'.
                                'DOB: '.($response['dob'] ?? 'N/A'),
                            'status' => 'successful',
                              'resp_code' => '200',
                        ]);
                } elseif (isset($response['status']) && $response['status'] === 'Failed') {
                    $service = \App\Models\Service::where('service_code', '112')->where('status', 'enabled')->first();
                    if (! $service) {
                        continue;
                    }

                    $serviceFee = $service->amount;
                    $wallet = \App\Models\Wallet::where('user_id', $userId)->first();
                    $balance = $wallet->balance + $serviceFee;

                    $refunded = \App\Models\IpeRequest::where('trackingId', $trackingId)
                        ->where('user_id', $userId)
                        ->whereNull('refunded_at')
                        ->first();

                    if ($refunded) {
                        $wallet->update(['balance' => $balance]);
                        $refunded->update([
                            'refunded_at' => Carbon::now(),
                            'reply' => $response['reply'],
                            'status' => 'failed',
                            'resp_code' => '400',
                        ]);

                        app(\App\Services\TransactionService::class)->createTransaction(
                            $userId,
                            $serviceFee,
                            'IPE Refund',
                            "IPE Refund for Tracking ID: {$trackingId}",
                            'Wallet',
                            'Approved'
                        );
                    }
                }

                // Optionally handle "New" status if needed
            } catch (\Exception $e) {
                Log::error("Error checking IPE status for $trackingId: ".$e->getMessage());
            }
        }
        Log::info('Done checking IPE requests.');
    }
}
