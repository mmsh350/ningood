<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\BankService;
use App\Models\Service;
use Illuminate\Support\Facades\Log;

class BankDataService
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.bank_data.url');
    }

    /**
     * Fetch bank services data from API and update database
     */
    public function fetchAndUpdateBankServices()
    {
        try {

            $url = $this->apiUrl;
            $token = config('services.verify_user.token');

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options for GET request
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            // Optionally decode JSON response
            $data = json_decode($response, true);

            // You can still log if needed
            Log::info('Fetched user verification response: ', $data ?? ['raw' => $response]);

            if ($data['success']) {
                $this->updateDatabase($data['data']);

                return ['success' => true, 'message' => 'Bank services updated successfully'];
            }

            return ['success' => false, 'message' => 'Failed to fetch data from API'];
        } catch (\Exception $e) {
            Log::error('Error fetching bank services: '.$e->getMessage());

            return ['success' => false, 'message' => 'Error: '.$e->getMessage()];
        }
    }

    /**
     * Update database with fetched data
     */
    protected function updateDatabase($bankServicesData)
    {
        foreach ($bankServicesData as $bankName => $services) {
            // Create or update bank
            $bank = Bank::firstOrCreate(['name' => $bankName]);

            foreach ($services as $serviceCode => $price) {
                $serviceName = $this->formatServiceName($serviceCode);

                // Create or update service
                $service = Service::firstOrCreate(
                    ['service_code' => $serviceCode],
                    ['name' => $serviceName]
                );

                // Update or create bank service - only update price, preserve commission
                BankService::updateOrCreate(
                    [
                        'bank_id' => $bank->id,
                        'service_id' => $service->id,
                    ],
                    ['price' => $price]
                    // Commission is not included here, so it won't be overwritten
                );
            }
        }
    }

    /**
     * Format service name from code
     */
    protected function formatServiceName($code)
    {
        return ucwords(str_replace('_', ' ', $code));
    }

    /**
     * Get current bank services with prices
     */
    public function getBankServices()
    {
        return Bank::with(['bankServices.service'])
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function ($bank) {
                $services = $bank->bankServices->mapWithKeys(function ($bankService) {
                    return [
                        $bankService->service->code => [
                            'price' => $bankService->price,
                            'commission' => $bankService->commission,
                            'total_price' => $bankService->total_price,
                        ],
                    ];
                });

                return [$bank->name => $services];
            });
    }
}
