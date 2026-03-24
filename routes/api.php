<?php

use App\Http\Controllers\APIController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum', 'accept.json', 'user.active')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/ipe', [APIController::class, 'ipeRequest']);
    Route::post('/ipe-status', [APIController::class, 'ipeRequestStatus']);

    Route::post('/nin-validation', [APIController::class, 'validationRequest']);
    Route::post('/nin-validation-status', [APIController::class, 'ValidationRequestStatus']);

});
