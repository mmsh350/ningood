<?php

namespace App\Console\Commands;

use App\Models\NinValidation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckNinValidationStatus extends Command
{
    protected $signature = 'app:check-nin-validation-status';

    protected $description = 'Check and update status of NIN validation requests';

    public function handle()
    {

        Log::info('Checking NIN validation statuses...');

        $records = NinValidation::whereIn('status', ['Pending', 'In-Progress'])
            ->whereNull('tag')
            ->get();

        foreach ($records as $record) {
            try {
                $url = env('BASE_API_URL_s8v').'/api/validation/status';
                $token = env('API_TOKEN_s8v');

                $payload = ['nin' => $record->nin_number, 'token' => $token];
                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                curl_close($ch);

                $response = json_decode($response, true);

                if (! isset($response['status'])) {
                    Log::warning("Missing status in response for NIN {$record->nin_number}");

                    continue;
                }

                $apiStatus = $response['status'];
                $reason = $response['reply'] ?? null;

                // ✅ Only update if status is one of your allowed values

                if (in_array($apiStatus, ['Successful', 'Failed', 'In-Progress'])) {

                    $resp_code = match ($apiStatus) {
                        'Successful' => '200',
                        'Failed' => '400',
                        'In-Progress' => '101',
                        default => '100',
                    };

                    NinValidation::where('id', $record->id)->update([
                        'status' => $apiStatus,
                        'reason' => $reason,
                        'resp_code' => $resp_code,
                    ]);

                    Log::info("NIN {$record->nin_number} updated to $apiStatus");
                } else {
                    Log::info("NIN {$record->nin_number} returned unhandled status: $apiStatus");
                }
            } catch (\Exception $e) {
                Log::error("Failed for NIN {$record->nin_number}: ".$e->getMessage());
            }
        }

        Log::info('NIN status check complete.');
    }
}
