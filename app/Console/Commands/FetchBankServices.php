<?php

namespace App\Console\Commands;

use App\Services\BankDataService;
use Illuminate\Console\Command;

class FetchBankServices extends Command
{
    protected $signature = 'bank-services:fetch';

    protected $description = 'Fetch bank services data from API and update database';

    public function handle(BankDataService $bankDataService)
    {
        $this->info('Fetching bank services data...');

        $result = $bankDataService->fetchAndUpdateBankServices();

        if ($result['success']) {
            $this->info('Bank services updated successfully!');
        } else {
            $this->error('Error: '.$result['message']);
        }
    }
}
