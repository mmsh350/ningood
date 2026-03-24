<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\BankService;
use App\Models\Service;
use Illuminate\Database\Seeder;

class BvnModificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // define banks
        $banks = [
            'First bank of Nigeria',
            'Agency',
            'Bank of agric',
            'Heritage bank',
            'NIBSS',
            'Microfinance bank',
            'Lapo',
            'Keystone',
        ];

        foreach ($banks as $bankName) {
            Bank::firstOrCreate(['name' => $bankName], ['is_active' => true]);
        }

        // define services
        $services = [
            [
                'name' => 'Correction of Name',
                'service_code' => 'CORRECTION_NAME',
                'price' => 1000.00,
            ],
            [
                'name' => 'Correction of Date of Birth',
                'service_code' => 'CORRECTION_DOB',
                'price' => 1000.00,
            ],
            [
                'name' => 'Correction of Phone Number',
                'service_code' => 'CORRECTION_PHONE',
                'price' => 500.00,
            ],
            [
                'name' => 'Correction of Name & Date of Birth',
                'service_code' => 'CORRECTION_NAME_DOB',
                'price' => 1500.00,
            ],
            [
                'name' => 'Correction of Gender',
                'service_code' => 'CORRECTION_GENDER',
                'price' => 500.00,
            ],
            [
                'name' => 'Correction of Address',
                'service_code' => 'CORRECTION_ADDRESS',
                'price' => 500.00,
            ],
            [
                'name' => 'BVN Revalidation',
                'service_code' => 'BVN_REVALIDATION',
                'price' => 1000.00,
            ],
            [
                'name' => 'BVN Deletion',
                'service_code' => 'BVN_DELETION',
                'price' => 2000.00,
            ],
        ];

        foreach ($services as $serviceData) {
            $service = Service::firstOrCreate(
                ['service_code' => $serviceData['service_code']],
                [
                    'name' => $serviceData['name'],
                    'category' => 'Verifications', // Or Agency
                    'amount' => $serviceData['price'],
                    'status' => 'enabled',
                    'description' => $serviceData['name'],
                ]
            );

            // Link to all banks
            $allBanks = Bank::all();
            foreach ($allBanks as $bank) {
                BankService::updateOrCreate(
                    [
                        'bank_id' => $bank->id,
                        'service_id' => $service->id,
                    ],
                    [
                        'price' => $serviceData['price'],
                        'commission' => 0,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
