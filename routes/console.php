<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Optimized Scheduler for Shared Hosting (Matching Hourly server cron)
Schedule::command('app:check-bvn-phone-status')->hourly()->withoutOverlapping();
Schedule::command('app:check-ipe-request-status')->hourly()->withoutOverlapping();
Schedule::command('app:check-self-service-status')->hourly()->withoutOverlapping();
Schedule::command('app:check-ipe-v3-status')->hourly()->withoutOverlapping();

// The following are disabled per your latest changes
// Schedule::command('app:check-bvn-request-status')->hourly()->withoutOverlapping();
// Schedule::command('app:check-personalize-status')->hourly()->withoutOverlapping();
//
// Schedule::command('bank-services:fetch')->daily()->withoutOverlapping();
