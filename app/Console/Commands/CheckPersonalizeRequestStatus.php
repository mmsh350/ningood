<?php

namespace App\Console\Commands;

use App\Models\PersonalizeRequest;
use App\Models\Service;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPersonalizeRequestStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-personalize-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status of pending or in-progress personalization requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Log::info('Checking pending and in-progress personalization requests...');

        $requests = PersonalizeRequest::whereIn('status', ['Pending', 'In-progress'])
            ->whereNull('tag')
            ->get();

        foreach ($requests as $request) {
            try {
                $trackingId = $request->tracking_no;
                $userId = $request->user_id;

                $url = env('BASE_API_URL_s8v').'/api/verification/status';
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

                if (isset($response['status']) && $response['status'] === 'Successful') {
                    $data = $response['data'];
                    $name = $data['firstName'].' '.$data['middleName'].' '.$data['lastName'];
                    $nin = $data['idNumber'];
                    $json = json_encode($data);

                    PersonalizeRequest::where('tracking_no', $trackingId)
                        ->where('user_id', $userId)
                        ->whereNull('tag')
                        ->update([
                            'reply' => $json,
                            'status' => 'Successful',
                            'name' => $name,
                            'nin' => $nin,
                            'comments' => 'Successful',
                        ]);
                } elseif (isset($response['status']) && $response['status'] === 'Failed') {
                    // $service = Service::where('service_code', '129')->where('status', 'enabled')->first();
                    // if (! $service) {
                    //     Log::warning("Service fee not found for user $userId and tracking $trackingId");

                    //     continue;
                    // }

                    // $serviceFee = $service->amount;
                    // $wallet = Wallet::where('user_id', $userId)->first();
                    // $refunded = PersonalizeRequest::where('tracking_no', $trackingId)
                    //     ->where('user_id', $userId)
                    //     ->whereNull('refunded_at')
                    //     ->whereNull('tag')
                    //     ->first();

                    // if ($refunded && $wallet) {
                    //     $wallet->update(['balance' => $wallet->balance + $serviceFee]);

                    $replyData = $response['status'].' '.($response['data']['idNumber'] ?? 'N/A');
                    $json = json_encode($replyData);

                    PersonalizeRequest::where('tracking_no', $trackingId)
                        ->where('user_id', $userId)->update([
                            'reply' => $json,
                            'status' => 'Failed',
                            'comments' => ($response['data']['idNumber'] ?? ''),
                        ]);

                    //     app(\App\Services\TransactionService::class)->createTransaction(
                    //         $userId,
                    //         $serviceFee,
                    //         'Personalization Refund',
                    //         "Personalization Refund for Tracking ID: {$trackingId}",
                    //         'Wallet',
                    //         'Approved'
                    //     );
                    // }
                } else {
                    Log::info("Status not updated for tracking $trackingId. API response: ".json_encode($response));
                }
            } catch (\Exception $e) {
                Log::error("Error checking personalization status for $trackingId: ".$e->getMessage());
            }
        }

        Log::info('Done checking personalization requests.');
    }
}
