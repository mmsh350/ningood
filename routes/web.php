<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankServiceController;
use App\Http\Controllers\BusinessRegistrationController;
use App\Http\Controllers\CompanyRegistrationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\EnrollmentSyncController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\IpeController;
use App\Http\Controllers\IpeWebhookController;
use App\Http\Controllers\ModIpeController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PopupController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserServicePriceController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\WalletController;
use App\Http\Requests\LoginRequest;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $settings = SiteSetting::first();
    if (! $settings->home_enabled) {
        return redirect()->away('https://');
    }

    return view('welcome');
});

Route::post('/palmpay/webhook', [PaymentWebhookController::class, 'handlePalmPay']);

Route::post('/ipe/webhook', [IpeWebhookController::class, 'handleWebhook']);

Route::post('/update-bvn-enrollment-status', [EnrollmentSyncController::class, 'updateStatus']);

Route::group(['as' => 'auth.', 'prefix' => 'auth', 'middleware' => 'guest'], function () {

    $settings = SiteSetting::first();

    if ($settings && $settings->login_enabled) {
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login']);
    } else {
        Route::match(['get', 'post'], 'login', function (HttpRequest $request) {

            if ($request->isMethod('post')) {
                $user = User::where('email', $request->input('email'))->first();

                if ($user && $user->role == 'admin' || $user->role == 'super admin') {

                    $loginRequest = app(LoginRequest::class);

                    return app(AuthController::class)->login($loginRequest);
                }
            }

            if ($request->isMethod('get') && $request->query('admin') == 1) {
                return app(AuthController::class)->showLoginForm($request);
            }

            return redirect()->away('https://');
        })->name('login');
    }

    // REGISTER ROUTES
    if ($settings && $settings->register_enabled) {
        Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    } else {
        Route::any('register', function () {
            return redirect()->away('https://');
        })->name('register');
    }

    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// User Routes
Route::middleware(['auth', 'user.active'])->group(function () {
    // User dashboard
    Route::group(['as' => 'user.', 'prefix' => 'user'], function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/verify-user', [VerificationController::class, 'verifyUser'])->name('verify-user');

        Route::post('/regenerate-token', [UserController::class, 'regenerateToken'])->name('regenerate.token');

        Route::middleware(['user.active', 'user.is_kyced'])->group(function () {
            Route::get('/verify-nin', [VerificationController::class, 'ninVerify'])->name('verify-nin');
            Route::get('/verify-nin2', [VerificationController::class, 'ninVerify2'])->name('verify-nin2');
            Route::get('/verify-nin3', [VerificationController::class, 'ninVerify3'])->name('verify-nin3');
            Route::get('/verify-nin4', [VerificationController::class, 'ninVerify4'])->name('verify-nin4');

             //Fantastic v4
             Route::get('/verify-nin5', [VerificationController::class, 'ninVerify5'])->name('verify-nin5');
             Route::get('/verify-phone-v5', [VerificationController::class, 'phoneVerifyV5'])->name('verify-phone-v5');
             Route::get('/verify-demo-v5', [VerificationController::class, 'demoVerifyV5'])->name('verify-demo-v5');

             //Fantastic v5
            Route::get('/verify-nin6', [VerificationController::class, 'ninVerify6'])->name('verify-nin6');

            Route::get('/verify-nin-phone', [VerificationController::class, 'phoneVerify'])->name('verify-nin-phone');
            Route::get('/verify-bvn', [VerificationController::class, 'bvnVerify'])->name('verify-bvn');

            Route::get('/nin-personalize', [VerificationController::class, 'ninPersonalize'])->name('personalize-nin');
            // Route::get('/ipe', [VerificationController::class, 'showIpe'])->name('ipe');
            Route::get('/bvn-enrollment', [EnrollmentController::class, 'bvnEnrollment'])->name('bvn-enrollment');
            Route::get('/verify-demo', [VerificationController::class, 'demoVerify'])->name('verify-demo');

            Route::get('/statusPersonalize/{id}', [VerificationController::class, 'statusPersonalize'])->name('statusPersonalize');
            Route::post('/sendPersonalize', [VerificationController::class, 'sendPersonalize'])->name('sendPersonalize');

             Route::get('/ipe/v3', [VerificationController::class, 'ShowIpeV3'])->name('ipe.v3');
            Route::post('/ipe-request/v3', [VerificationController::class, 'ipeRequestV3'])->name('ipe.request.v3');
            Route::get('/ipeStatusV3/{id}', [VerificationController::class, 'ipeRequestStatusV3'])->name('ipeStatusV3');

            // Ipe request

            // Route::post('/ipe-request', [VerificationController::class, 'ipeRequest'])->name('ipe-request');

            // Route::post('/ipe-bulk-request', [VerificationController::class, 'ipeBulkRequest'])
            //     ->name('ipe-bulk-request');

            // Route::get('/ipeStatus/{id}', [VerificationController::class, 'ipeRequestStatus'])->name('ipeStatus');

            // Enrollment-----------------------------------------------------------------------------------------------------
            Route::post('/bvn-enrollment', [EnrollmentController::class, 'enrollBVN'])->name('enroll-bvn');
            // Wallet
            Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
            Route::get('claim-bonus/{id}', [WalletController::class, 'claimBonus'])->name('claim-bonus');

            // Transactions -----------------------------------------------------------------------------------------------------
            Route::get('/receipt/{referenceId}', [TransactionController::class, 'reciept'])->name('reciept');

            // Verification-----------------------------------------------------------------------------------------------------
            // NIN
            Route::post('/nin-retrieve', [VerificationController::class, 'ninRetrieve'])->name('ninRetrieve');
            Route::post('/nin-phone-retrieve', [VerificationController::class, 'ninPhoneRetrieve'])->name('ninPhoneRetrieve');
            Route::post('/nin-track-retrieve', [VerificationController::class, 'ninTrackRetrieve'])->name('ninTrackRetrieve');
            Route::post('/nin-demo-retrieve', [VerificationController::class, 'ninDemoRetrieve'])->name('nin-demo-Retrieve');
            Route::post('/nin-v2-retrieve', [VerificationController::class, 'ninV2Retrieve'])->name('nin-v2-Retrieve');

            //Fantastic v4
            Route::post('/nin-v5-retrieve', [VerificationController::class, 'ninV5Retrieve'])->name('nin-v5-Retrieve');
            Route::post('/nin-v5-phone-retrieve', [VerificationController::class, 'ninV5PhoneRetrieve'])->name('nin-v5-phone-Retrieve');
            Route::post('/nin-v5-demo-retrieve', [VerificationController::class, 'ninDemoRetrieveV5'])->name('nin-v5-demo-Retrieve');

            Route::post('/nin-v6-retrieve', [VerificationController::class, 'ninV6Retrieve'])->name('nin-v6-Retrieve');


            // BVN
            Route::post('/bvn-retrieve', [VerificationController::class, 'bvnRetrieve'])->name('bvnRetrieve');

            Route::get('/verify-bvn2', [VerificationController::class, 'bvnPhoneVerify'])->name('verify-bvn2');
            Route::post('/bvn-retrieve2', [VerificationController::class, 'bvnPhoneRetrieve'])->name('bvnRetrieve2');
            Route::post('/nin-v3-retrieve', [VerificationController::class, 'ninV3Retrieve'])->name('nin-v3-Retrieve');
            Route::post('/nin-v4-retrieve', [VerificationController::class, 'ninV4Retrieve'])->name('nin-v4-Retrieve');

            Route::get('/nin-delink', [ServicesController::class, 'ninDelink'])->name('nin.delink');
            Route::post('/nin-services/delink/request', [ServicesController::class, 'requestNinServiceDelink'])->name('nin.services.delink.request');

            //
            Route::get('/bvn-phone-search', [VerificationController::class, 'bvnPhoneSearch'])->name('bvn-phone-search');
            Route::post('bvn-phone-search', [VerificationController::class, 'bvnPhoneRequest'])->name('bvn-phone-request');

            // PDF Downloads -----------------------------------------------------------------------------------------------------
            Route::get('/standardBVN/{id}', [VerificationController::class, 'standardBVN'])->name('standardBVN');
            Route::get('/premiumBVN/{id}', [VerificationController::class, 'premiumBVN'])->name('premiumBVN');
            Route::get('/plasticBVN/{id}', [VerificationController::class, 'plasticBVN'])->name('plasticBVN');

            Route::get('/regularSlip/{id}', [VerificationController::class, 'regularSlip'])->name('regularSlip');
            Route::get('/standardSlip/{id}', [VerificationController::class, 'standardSlip'])->name('standardSlip');
            Route::get('/premiumSlip/{id}', [VerificationController::class, 'premiumSlip'])->name('premiumSlip');
            Route::get('/basicSlip/{id}', [VerificationController::class, 'basicSlip'])->name('basicSlip');

            Route::get('/verify-tin', [VerificationController::class, 'tinVerify'])->name('verify-tin');
            Route::post('/tin-retrieve', [VerificationController::class, 'tinRetrieve'])->name('tinRetrieve');
            Route::get('/tinSlip/{id}/{type}', [VerificationController::class, 'tinSlip'])->name('tinSlip');

            // NIN Services
            Route::get('/nin-services', [ServicesController::class, 'ninServices'])->name('nin.services');
            Route::post('/nin-services/request', [ServicesController::class, 'requestNinService'])->name('nin.services.request');
            Route::post('/nin-services/request-bulk', [ServicesController::class, 'requestBulkNinService'])->name('nin.services.bulk');

            Route::get('/ninStatus/{id}', [ServicesController::class, 'ninRequestStatus'])->name('ninStatus');

            Route::get('/nin-mod', [ServicesController::class, 'ninModification'])->name('nin.mod');
            Route::post('/nin-services/mod', [ServicesController::class, 'requestModification'])->name('nin.services.mod');
            Route::get('/nin-modification/{id}/edit', [ServicesController::class, 'editNinModification'])->name('nin-modification.edit');
            Route::put('/nin-modification/{id}', [ServicesController::class, 'updateNinModification'])->name('nin-modification.update');

            // NIN Mod ipe
            Route::get('/nin-mod-ipe', [ModIpeController::class, 'ninModIpe'])->name('nin.mod.ipe');
            Route::post('/nin-mod/ipe/request', [ModIpeController::class, 'requestNinServiceIPE'])->name('nin.mod.ipe.request');

            Route::get('/business-registration', [BusinessRegistrationController::class, 'create'])->name('business.create');
            Route::post('/business-registration', [BusinessRegistrationController::class, 'store'])->name('business.store');
            Route::get('/business-registration/{id}/edit', [BusinessRegistrationController::class, 'edit'])->name('business.edit');
            Route::put('/business-registration/{id}', [BusinessRegistrationController::class, 'update'])->name('business.update');

            Route::get('/company-registration', [CompanyRegistrationController::class, 'create'])->name('company.create');
            Route::post('/company-registration', [CompanyRegistrationController::class, 'store'])->name('company.store');
            Route::get('/company-registration/{id}/edit', [CompanyRegistrationController::class, 'edit'])->name('company.edit');
            Route::put('/company-registration/{id}', [CompanyRegistrationController::class, 'update'])->name('company.update');

             //Whatsapp API Support--------------------------------------------------------------------------
            Route::get('/support', function () {
                $settings = SiteSetting::first();
                return redirect()->away($settings->whatsapp_url??env('API_URL'));
            })->name('support');

        });

        // BVN Modification
        Route::get('/bank-services', [BankServiceController::class, 'index'])->name('bank-services.index');
        Route::get('/banks/{bank}/services', [BankServiceController::class, 'getServices'])->name('banks.services');
        Route::post('/modification-requests-action', [BankServiceController::class, 'storeRequest'])->name('modification-requests.action');
        Route::get('/bvn-modification/{id}/edit', [BankServiceController::class, 'editRequest'])->name('bvn-modification.edit');
        Route::put('/bvn-modification/{id}', [BankServiceController::class, 'updateRequest'])->name('bvn-modification.update');

        Route::get('/profile', function () {
            return view('user.profile');
        })->name('profile');

        Route::get('/apidocs', function () {
            return view('user.apidocs');
        })->name('api.docs');

        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
    });
    // Logout Route
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// Admin Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'user.active', 'user.admin']], function () {
    Route::get('/receipt/{referenceId}', [TransactionController::class, 'recieptAdmin'])->name('reciept');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('user.update');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('user.activate');

    Route::get('/transactions', [TransactionController::class, 'transactions'])->name('transactions');

    Route::get('/bvn-services', [ServicesController::class, 'bvnServicesList'])->name('bvn.services.list');
    Route::post('/requests/{id}/{type}/update-bvn-status', [ServicesController::class, 'updateBvnRequestStatus'])->name('bvn-update-request-status');
    Route::get('/view-bvn-request/{id}/{type}/edit', [ServicesController::class, 'showBvnRequests'])->name('bvn-view-request');

    Route::get('/mod-services', [ServicesController::class, 'modServicesList'])->name('mod.services.list');
    Route::post('/requests/{id}/{type}/update-mod-status', [ServicesController::class, 'updateModRequestStatus'])->name('mod-update-request-status');
    Route::get('/view-mod-request/{id}/{type}/edit', [ServicesController::class, 'showModRequests'])->name('mod-view-request');

    // bvn modification
    Route::get('/bvn-modification', [BankServiceController::class, 'adminList'])->name('bvn-modification.index');
    Route::post('/bvn-modification/{id}/update-status', [BankServiceController::class, 'updateStatus'])->name('bvn-modification.update-status');
    Route::get('/bank-services/manage', [BankServiceController::class, 'manageBankServices'])->name('bank-services.manage');
    Route::post('/bank-services/update-price', [BankServiceController::class, 'updateBankServicePrice'])->name('bank-services.update-price');

    // Services
    Route::get('/services', [ServicesController::class, 'index'])->name('services.index');
    Route::get('/services/edit/{id}', [ServicesController::class, 'edit'])->name('services.edit');
    Route::put('/services/update/{id}', [ServicesController::class, 'update'])->name('services.update');

    Route::get('/popup', [PopupController::class, 'index'])->name('popup.index');
    Route::post('/save-popup', [PopupController::class, 'store'])->name('popup.store');

    // NIN Services
    Route::get('/nin-services', [ServicesController::class, 'ninServicesList'])->name('nin.services.list');
    Route::get('/delink-services', [ServicesController::class, 'delinkServicesList'])->name('delink.services.list');
    Route::post('/requests/{id}/{type}/update-status', [ServicesController::class, 'updateRequestStatus'])->name('update-request-status');
    Route::get('/view-request/{id}/{type}/edit', [ServicesController::class, 'showRequests'])->name('view-request');

    // export and upload validation
    Route::get('validation/download-template', [ExportController::class, 'downloadTemplateValidation'])->name('validation.download-template');
    Route::post('validation/upload-excel', [ExportController::class, 'uploadExcelForNINValidation'])->name('validation.upload-excel');

    Route::get('site-settings/edit', [SiteSettingController::class, 'edit'])->name('site-settings.edit');
    Route::put('site-settings', [SiteSettingController::class, 'update'])->name('site-settings.update');

    Route::get('/export-bvn', [ExportController::class, 'exportBvnSearh'])->name('export.bvnSearch');
    Route::get('/export-nin', [ExportController::class, 'exportNINService'])->name('export.ninService');

    Route::get('mod-ipe-index', [ModIpeController::class, 'ninServicesList'])->name('modipe.index');
    Route::get('/mod-ipe-clearance/export', [ExportController::class, 'exportModIpe'])->name('export.modipe');
    Route::post('/requests/{id}/{type}/update-mod-ipe-status', [ModIpeController::class, 'updateRequestStatus'])->name('update-mod-ipe-status');

    // BVN User Rerquest
    Route::get('/enrollment-list', [EnrollmentController::class, 'index'])->name('enroll.index');
    Route::post('/requests/{id}/{type}/update-status2', [EnrollmentController::class, 'updateRequestStatus'])->name('update-request-status2');
    Route::get('/view-request2/{id}/{type}/edit', [EnrollmentController::class, 'showRequests'])->name('view-request2');

    Route::get('/cac-biz-list', [BusinessRegistrationController::class, 'index'])->name('business-reg');
    Route::get('/view-request3/{id}/{type}/edit', [BusinessRegistrationController::class, 'showRequests'])->name('view-request3');
    Route::post('/requests/{id}/{type}/update-status3', [BusinessRegistrationController::class, 'updateRequestStatus'])->name('update-request-status3');

    Route::get('/company-registrations', [CompanyRegistrationController::class, 'index'])->name('company.index');
    Route::get('/company-registrations/{id}/view', [CompanyRegistrationController::class, 'show'])->name('company.show');
    Route::post('/company-registrations/{id}/update-status', [CompanyRegistrationController::class, 'updateStatus'])->name('company.update-status');

    Route::post('{user}/service-prices', [UserServicePriceController::class, 'store'])->name('service-prices.store');

    Route::delete('/service-prices/{price}', [UserServicePriceController::class, 'destroy'])->name('service-prices.destroy');

    Route::get('ipe-index', [IpeController::class, 'ipeIndex'])->name('ipe.index');
    Route::get('ipe/download-template', [IpeController::class, 'downloadTemplateIPE'])->name('ipe.download-template');
    Route::post('ipe/upload-excel', [IpeController::class, 'uploadExcelIPE'])->name('ipe.upload-excel');
    Route::get('/ipe/refund-failed', [IpeController::class, 'refundFailedTransactions'])->name('ipe.refund');

});
