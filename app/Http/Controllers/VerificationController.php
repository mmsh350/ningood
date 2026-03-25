<?php

namespace App\Http\Controllers;

use App\Http\Repositories\BVN_PDF_Repository;
use App\Http\Repositories\NIN_PDF_Repository;
use App\Http\Repositories\VirtualAccountRepository;
use App\Http\Repositories\WalletRepository;
use App\Models\BvnPhoneSearch;
use App\Models\IpeRequest;
use App\Models\PersonalizeRequest;
use App\Models\Service;
use App\Models\UserServicePrice;
use App\Models\Verification;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    protected $transactionService;

    protected $loginId;

    const RESP_STATUS_SUCCESS = true;

    const RESP_MESSAGE = null;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = auth()->user()->id;
    }

    public function demoVerify()
    {

        $serviceCodes = ['116', '106', '107', '105', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('116') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        $user = auth()->user();

        $latestVerifications = $user->verifications()->latest()->paginate(5);

        return view('verification.demo-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee', 'regular_nin_fee', 'basic_nin_fee', 'latestVerifications'));
    }

    public function demoVerifyV5()
    {

        $serviceCodes = ['149', '106', '107', '105', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('149') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        $user = auth()->user();

        $latestVerifications = $user->verifications()->latest()->paginate(5);

        return view('verification.demo-verify-v5', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee', 'regular_nin_fee', 'basic_nin_fee', 'latestVerifications'));
    }

    public function ninDemoRetrieve(Request $request)
    {

        $request->validate([
            'gender' => ['required', 'in:MALE,FEMALE'],
            'dob' => ['required', 'date'],
            'lastName' => ['required', 'string', 'max:255'],
            'firstName' => ['required', 'string', 'max:255'],
        ]);

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '116')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = [
                    'firstName' => $request->input('firstName'),
                    'lastName' => $request->input('lastName'),
                    'dob' => $request->input('dob'),
                    'gender' => $request->input('gender'),
                ];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-demo';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN DEMO Vericiation', $response);

                if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS && $response['message'] !== 'norecord') {

                    $data = $response['message'];

                    $this->processResponseDataForNINDEMO($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Demo Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS && $response['message'] === 'norecord') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                } elseif (isset($response['status']) && $response['status'] === 'caption') {

                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Caption: '.$response['message']],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }
     public function ninDemoRetrieveV5(Request $request)
    {

        $request->validate([
            'gender' => ['required', 'in:MALE,FEMALE'],
            'dob' => ['required', 'date'],
            'lastName' => ['required', 'string', 'max:255'],
            'firstName' => ['required', 'string', 'max:255'],
        ]);

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '149')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = [
                    'firstName' => $request->input('firstName'),
                    'lastName' => $request->input('lastName'),
                    'dob' => $request->input('dob'),
                    'gender' => $request->input('gender'),
                ];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin/v4/demo';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN DEMO Vericiation', $response);

                if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS) {

                    $data = $response['message'];

                    $this->processResponseDataForNINV5DEMO($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Demo Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['status']) && $response['status'] === false) {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                }elseif($response['respCode'] === '102'){

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                }
                 else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninv4Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '145')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin/v3';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN V3 Vericiation', $response);

                if (isset($response['respCode']) && $response['respCode'] == '000') {

                    $data = $response['message'];

                    $this->processResponseDataForNINPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V3 Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (in_array($response['respCode'], ['100', '101', '102', '103'])) {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V3 Verification', $serviceDesc, 'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                } else {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V3 Verification', $serviceDesc, 'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninVerify2()
    {

        $serviceCodes = ['118', '105', '106', '107', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('118') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        return view('verification.nin-verifyv2', compact('ServiceFee', 'regular_nin_fee', 'standard_nin_fee', 'premium_nin_fee', 'basic_nin_fee'));
    }

    public function ninVerify4()
    {

        $serviceCodes = ['145', '105', '106', '107', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('145') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        return view('verification.nin-verifyv4', compact('ServiceFee', 'regular_nin_fee', 'standard_nin_fee', 'premium_nin_fee', 'basic_nin_fee'));
    }

    public function ninVerify5()
    {

        $serviceCodes = ['149', '105', '106', '107', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('149') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        return view('verification.nin-verifyv5', compact('ServiceFee', 'regular_nin_fee', 'standard_nin_fee', 'premium_nin_fee', 'basic_nin_fee'));
    }
    public function ninVerify6()
    {

        $serviceCodes = ['150', '105', '106', '107', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('150') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        return view('verification.nin-verifyv6', compact('ServiceFee', 'regular_nin_fee', 'standard_nin_fee', 'premium_nin_fee', 'basic_nin_fee'));
    }

    public function ninv2Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '118')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin/v2';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN V2 Vericiation', $response);

                if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS && $response['message'] !== 'norecord') {

                    $data = $response['message'];

                    $this->processResponseDataForNINPhone($data); // same as nin phone response

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V2 Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS && $response['message'] === 'norecord') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                } elseif (isset($response['status']) && $response['status'] === 'caption') {

                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Caption: '.$response['message']],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninv5Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '149')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin/v4';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN V4 Vericiation', $response);

                if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS) {

                    $data = $response['message'];

                    $this->processResponseDataForNINPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V4 Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['status']) && $response['status'] === false && $response['respCode'] === '102') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                }
             else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninv6Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '150')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin/v5';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                // Log response
                Log::info('NIN V5 Vericiation', $response);

                if (isset($response['status']) && $response['status'] === self::RESP_STATUS_SUCCESS) {

                    $data = $response['message'];

                    $this->processResponseDataForNINPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V6 Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['status']) && $response['status'] === false && $response['respCode'] === '102') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['No record found'],
                    ], 422);
                }
             else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }
    public function ShowIpe(Request $request)
    {
        $serviceCodes = ['112'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('112') ?? 0.00;

        $now = Carbon::now();

        $userPrice = UserServicePrice::where('user_id', auth()->id())
            ->where('service_id', $ServiceFee->id)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->latest()
            ->first();

        $ServiceFee = $userPrice
            ? $userPrice->custom_price
            : $ServiceFee->amount;

        $query = IpeRequest::where('user_id', auth()->id());

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('trackingId', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $ipes = $query->orderBy('id', 'desc')->paginate(10);

        $ipeStatusCounts = IpeRequest::selectRaw('status, COUNT(*) as count')
            ->where('user_id', auth()->id())
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAll = IpeRequest::where('user_id', auth()->id())->count();
        $totalPending = $ipeStatusCounts['pending'] ?? 0;
        $totalFailed = $ipeStatusCounts['failed'] ?? 0;
        $totalSuccessful = $ipeStatusCounts['successful'] ?? 0;

        return view('verification.ipe', compact(
            'ServiceFee',
            'ipes',
            'totalPending',
            'totalFailed',
            'totalSuccessful',
            'totalAll'
        ));
    }

    public function ninPersonalize(Request $request)
    {
        $serviceCodes = ['108', '105', '106', '107', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('108') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        $query = PersonalizeRequest::where('user_id', auth()->id())->whereNull('tag');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('refno', 'like', "%{$search}%")
                    ->orWhere('tracking_no', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $personalize = $query->orderBy('id', 'desc')->whereNull('tag')->paginate(10);

        // ✅ Status counts
        $statusCounts = PersonalizeRequest::selectRaw('status, COUNT(*) as count')
            ->where('user_id', auth()->user()->id)
            ->whereNull('tag')
            ->groupBy('status')
            ->pluck('count', 'status'); // returns ['Successful' => 5, 'Failed' => 3, ...]

        $totalAll = PersonalizeRequest::where('user_id', auth()->user()->id)->whereNull('tag')->count();
        $totalPending = $statusCounts['Pending'] ?? 0;
        $totalFailed = $statusCounts['Failed'] ?? 0;
        $totalInProgress = $statusCounts['In-Progress'] ?? 0;
        $totalSuccessful = $statusCounts['Successful'] ?? 0;

        return view('verification.nin-track', compact(
            'ServiceFee',
            'standard_nin_fee',
            'premium_nin_fee',
            'regular_nin_fee',
            'basic_nin_fee',
            'personalize',
            'totalAll',
            'totalPending',
            'totalFailed',
            'totalInProgress',
            'totalSuccessful'
        ));
    }

    public function sendPersonalize(Request $request)
    {

        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '108')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return redirect()->route('user.personalize-nin')
                ->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {

            return redirect()->route('user.personalize-nin')
                ->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            try {

                // $url = env('BASE_API_URL_s8v').'/api/personalization';
                $url = env('BASE_API_URL_s8v').'/api/personalization/speacial';
                $token = env('API_TOKEN_s8v');
                $data = ['tracking_id' => strtoupper($request->input('trackingId')), 'token' => $token];

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                Log::info('Personalization Response', $response);

                if (isset($response['status']) && $response['status'] === 'In-Progress' && $response['message'] === 'Verification processing') {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $transaction = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'Personalization Request', $serviceDesc, 'Wallet', 'Approved');

                    $this->processPersonalizeRequest($loginUserId, $request->input('trackingId'), $transaction->referenceId, $trx_id = $transaction->id);

                    return redirect()->route('user.personalize-nin')
                        ->with('success', 'Personalization request has been submitted successfully.');
                } elseif (isset($response['status']) && $response['status'] === 'In-Progress' && $response['message'] !== 'Verification processing') {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $transaction = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'Personalization Request', $serviceDesc, 'Wallet', 'Approved');

                    $this->processPersonalizeRequest($loginUserId, $request->input('trackingId'), $transaction->referenceId, $trx_id = $transaction->id);

                    return redirect()->route('user.personalize-nin')
                        ->with('success', 'Personalization request has been submitted successfully.');
                } elseif (isset($response['status']) && $response['status'] === 'Successful') {
                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $transaction = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'Personalization Request', $serviceDesc, 'Wallet', 'Approved');

                    $this->processPersonalizeRequest($loginUserId, $request->input('trackingId'), $transaction->referenceId, $trx_id = $transaction->id);

                    $this->statusPersonalize($request->input('trackingId'));

                    return redirect()->route('user.personalize-nin')
                        ->with('success', 'Personalization request has been submitted and processed successfully.');
                } else {
                    return redirect()->route('user.personalize-nin')
                        ->with('error', 'Unfortunately, our personalization request didn\'t go through this time. Don\'t worry, we\'re working on it and will get back to you soon with a solution.');
                }
            } catch (\Exception $e) {
                return redirect()->route('user.personalize-nin')
                    ->with('error', 'API: Unfortunately, our personalization request didn\'t go through this time. Don\'t worry, we\'re working on it and will get back to you soon with a solution.');
            }
        }
    }

    public function statusPersonalize($trackingId)
    {

        try {

            $url = env('BASE_API_URL_s8v').'/api/verification/status';
            $token = env('API_TOKEN_s8v');
            $data = ['tracking_id' => $trackingId, 'token' => $token];

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['status']) && $response['status'] === 'Successful') {

                $data = $response['data'];
                // insert to verification
                // $this->processPersonalizeRequestData($data);

                $name = $data['firstName'].' '.$data['middleName'].' '.$data['lastName'];
                $nin = $data['idNumber'];

                $data = json_encode($data);

                // insert json into personalization
                PersonalizeRequest::where('tracking_no', $trackingId)
                    ->where('user_id', $this->loginId)
                    ->update(['reply' => $data ?? 'N/A', 'status' => $response['status'] ?? 'Successful', 'name' => $name, 'nin' => $nin, 'comments' => 'Successful']);

                return redirect()->route('user.personalize-nin')
                    ->with('success', 'Personalization request was Successfull.');
            } elseif (isset($response['status']) && $response['status'] === 'Failed') {

                // // process refund & NIN Services Fee
                // $ServiceFee = 0;

                // $ServiceFee = Service::where('service_code', '129')
                //     ->where('status', 'enabled')
                //     ->first();

                // if (! $ServiceFee) {
                //     return redirect()->route('user.personalize-nin')
                //         ->with('error', 'Sorry Action not Allowed !');
                // }

                // $ServiceFee = $ServiceFee->amount;

                // $wallet = Wallet::where('user_id', $this->loginId)->first();

                // $balance = $wallet->balance + $ServiceFee;

                // // Check if already refunded
                // $refunded = PersonalizeRequest::where('tracking_no', $trackingId)
                //     ->where('user_id', $this->loginId)
                //     ->whereNull('refunded_at')
                //     ->first();

                $replyData = '';

                $replyData = $response['status'].' '.$response['data']['idNumber'];
                $json = json_encode($replyData);

                PersonalizeRequest::where('tracking_no', $trackingId)
                    ->where('user_id', $this->loginId)
                    ->update(['reply' => $json, 'status' => $response['status'], 'comments' => $response['data']['idNumber']]);

                // if ($refunded) {
                //     Wallet::where('user_id', $this->loginId)
                //         ->update(['balance' => $balance]);

                //     $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Personalization Refund', "Personalization Refund for Tracking ID: {$trackingId}", 'Wallet', 'Approved');
                // }

                return redirect()->route('user.personalize-nin')
                    ->with('error', $replyData);
            } else {
                return redirect()->route('user.personalize-nin')
                    ->with('error', isset($response['status'])
                        ? $response['status'].': '.($response['message'] ?? '')
                        : 'Cannot get status at the moment.');
            }
        } catch (\Exception $e) {
            return redirect()->route('user.personalize-nin')
                ->with('error', $e.'An error occurred while making the API request');
        }
    }

    public function ninVerify()
    {

        $serviceCodes = ['104', '106', '107'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('104') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;

        return view('verification.nin-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee'));
    }

    public function tinVerify()
    {

        $serviceCodes = ['147', '148'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('147') ?? 0.00;
        $standard_tin_fee = $services->get('148') ?? 0.00;

        return view('verification.tin-verify', compact('ServiceFee', 'standard_tin_fee'));
    }

    public function tinRetrieve(Request $request)
    {
        // Validate entity selection first
        $request->validate([
            'entity' => 'required|in:individual,corporate',
        ]);

        $entity = $request->input('entity');

        $payload = [];

        if ($entity === 'individual') {

            $request->validate([
                'nin' => 'required|numeric|digits:11',
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'dateOfBirth' => 'required|date',
            ], [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN must be numeric.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
                'firstName.required' => 'First name is required.',
                'lastName.required' => 'Last name is required.',
                'dateOfBirth.required' => 'Date of birth is required.',
                'dateOfBirth.date' => 'Date of birth must be a valid date.',
            ]);

            $payload = [
                'entity' => $entity,
                'nin' => $request->input('nin'),
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'dateOfBirth' => $request->input('dateOfBirth'),
            ];
        }

        // Corporate (RC) validation only when selected
        if ($entity === 'corporate') {
            $request->validate([
                'type' => 'required|in:1,2,3,4,5',
                'rc' => 'required|string|max:100',
            ], [
                'type.required' => 'Entity type is required for corporate submissions.',
                'type.in' => 'Invalid entity type selected.',
                'rc.required' => 'RC number is required for corporate submissions.',
            ]);

            $payload = [
                'entity' => $entity,
                'type' => (string) $request->input('type'),
                'rc' => $request->input('rc'),
            ];
        }


       // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '147')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

             try {

                $url = env('BASE_URL_VERIFY_USER').'api/v1/verify-tin';
                $token = env('VERIFY_USER_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                // $response = '{"status":"success","message":"TIN REGISTRATION Successful","data":{"dateOfBirth":"22/02/2002","firstName":"SHAFIU","lastName":"MUHAMMAD","nin":"51307511444","tax_id":"2512104317591","tax_residency":"Kebbi State"},"transaction_ref":"tin0643U87T0","charge":"80.00"}';
                // //   $response = '{"status":"success","message":"TIN REGISTRATION Successful","data":{"company_name":"OMNOSTOCK LIMITED","rc":"9084483","tax_id":"2523084691038","type":"3"},"transaction_ref":"tin5604M1UXG","charge":"100.00"}';
                $response = json_decode($response, true);
                Log::info('TIN Verification', $response);

                if ((isset($response['status']) && $response['status'] === "success")) {
                    $data = $response['data'] ?? [];

                    if ($entity === 'individual') {

                        $dob = $data['dateOfBirth'] ?? ($data['dob'] ?? null);
                        try {
                            if ($dob) {
                                $dt = \DateTime::createFromFormat('d/m/Y', $dob) ?: new \DateTime($dob);
                                $dobFormatted = $dt->format('Y-m-d');
                            } else {
                                $dobFormatted = null;
                            }
                        } catch (\Exception $e) {
                            $dobFormatted = null;
                        }

                        try {
                            Verification::create([
                                'idno' => $data['tax_id'] ?? $data['nin'] ?? null,
                                'type' => 'NIN',
                                'nin' => $data['nin'] ?? null,
                                'first_name' => $data['firstName'] ?? null,
                                'middle_name' => $data['middleName'] ?? null,
                                'last_name' => $data['lastName'] ?? null,
                                'dob' => $dobFormatted,
                                'address' => $data['tax_residency'] ?? null,
                                'gender' => 'M' ?? null,
                                'photo' => 'null',
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to create TIN individual verification: ' . $e->getMessage());
                        }
                    } else {
                        // corporate
                        try {
                            Verification::create([
                                'idno' => $data['tax_id'] ?? $data['rc'],
                                'type' => 'NIN',
                                'nin' => $data['rc'] ?? null,
                                'first_name' => $data['company_name'] ?? null,
                                'middle_name' =>  "NA",
                                'last_name' =>  "NA",
                                'gender' => 'M' ?? null,
                                'photo' => 'null',
                                'dob' => '1970-01-01',
                            ]);
                        } catch (\Exception $e) {
                            Log::warning('Failed to create TIN corporate verification: ' . $e->getMessage());
                        }
                    }

                    // Debit wallet and create transaction
                    $balance = $wallet->balance - $ServiceFee;
                    Wallet::where('user_id', $loginUserId)->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2);
                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'TIN Verification', $serviceDesc, 'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'success',
                        'entity' => $entity,
                        'data' => $data,
                        'transaction_ref' => $response['transaction_ref'] ?? null,
                        'charge' => $ServiceFee,
                    ]);
                }

                return response()->json([
                    'status' => $response['message'] ?? 'Verification Failed',
                    'errors' => [$response['message'] ?? 'Verification Failed'],
                ], 422);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }

        }
    }

    public function bvnVerify()
    {
        // Fetch all required service fees in one query
        $serviceCodes = ['101', '102', '103', '109'];
        $services = Service::whereIn('service_code', $serviceCodes)->get()->keyBy('service_code');

        $BVNFee = $services->get('101') ?? 0.00;
        $bvn_standard_fee = $services->get('102') ?? 0.00;
        $bvn_premium_fee = $services->get('103') ?? 0.00;
        $bvn_plastic_fee = $services->get('109') ?? 0.00;

        return view('verification.bvn-verify', compact('BVNFee', 'bvn_standard_fee', 'bvn_premium_fee', 'bvn_plastic_fee'));
    }

    public function phoneVerify()
    {

        $serviceCodes = ['111', '105', '106', '107'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('111') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;

        return view('verification.nin-phone-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee', 'regular_nin_fee'));
    }
    public function phoneVerifyV5()
    {

        $serviceCodes = ['149', '105', '106', '107'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('149') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;

        return view('verification.nin-phone-v5-verify', compact('ServiceFee', 'standard_nin_fee', 'premium_nin_fee', 'regular_nin_fee'));
    }

    private function createAccounts($userId)
    {

        $repObj = new WalletRepository;
        $repObj->createWalletAccount($userId);

        $repObj2 = new VirtualAccountRepository;
        $repObj2->createVirtualAccount($userId);
    }

    public function verifyUser(Request $request)
    {
        $request->validate([
            'bvn' => 'required|numeric|digits:11',
        ]);

        $bvn = $request->input('bvn');

        return $this->verifyUserBVN($bvn);
    }

    private function verifyUserBVN($bvn)
    {
        try {

            $data = ['bvn' => $bvn];

            $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-bvn';
            $token = env('VERIFY_USER_TOKEN2');
            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['respCode']) && $response['respCode'] == '000') {

                $data = $response['data'];

                $updateData = [
                    'name' => ucwords(strtolower($data['firstName']).' '.strtolower($data['middleName']).' '.strtolower($data['lastName'])),
                    'dob' => $data['birthday'],
                    'gender' => $data['gender'],
                    'kyc_status' => 'Verified',
                    'idNumber' => $bvn,
                ];

                if (! empty($data['phoneNumber'])) {
                    $updateData['phone_number'] = $data['phoneNumber'];
                }

                if (! empty($data['photo'])) {
                    $updateData['profile_pic'] = $data['photo'];
                }

                auth()->user()->update($updateData);

                $this->createAccounts(auth()->user()->id);

                return redirect()->back()->with('success', 'Your identity verification is complete, and youre all set to explore our services. Thank you for verifying your account!');
            } else {
                Log::error('Error Verifiying User '.auth()->user()->id.': '.$response);

                return redirect()->back()->with('error', 'An error occurred while making the BVN Verification (System Err)');
            }
        } catch (\Exception $e) {
            Log::error('Error Verifiying User '.auth()->user()->id.': '.$e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while making the BVN Verification');
        }
    }

    public function ninRetrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '104')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['nin' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['respCode']) && $response['respCode'] == '000') {

                    $data = $response['data'];

                    $this->processResponseDataForNIN($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif ($response['respCode'] == '99120010') {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Verification', $serviceDesc, 'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninPhoneRetrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The Phone number is required.',
                'nin.numeric' => 'The Phone number must be a numeric value.',
                'nin.digits' => 'The Phone must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '111')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['phone' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-phone';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['respCode']) && $response['respCode'] == '000') {

                    $data = $response['message'];

                    $this->processResponseDataForNINPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Phone Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif ($response['respCode'] == '103') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninV5PhoneRetrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The Phone number is required.',
                'nin.numeric' => 'The Phone number must be a numeric value.',
                'nin.digits' => 'The Phone must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '149')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['phone' => $request->input('nin')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-nin/v4/phone';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['respCode']) && $response['respCode'] == '000') {

                    $data = $response['message'];

                    $this->processResponseDataForNINPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Phone Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif ($response['respCode'] == '103') {

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ipeRequest(Request $request)
    {
        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '112')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return redirect()->route('user.ipe')
                ->with('error', 'Sorry Action not Allowed !');
        }

        $now = Carbon::now();
        $loginUserId = auth()->user()->id;

        $userPrice = UserServicePrice::where('user_id', $loginUserId)
            ->where('service_id', $ServiceFee->id)
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $now);
            })
            ->latest()
            ->first();

        $serviceFee = $userPrice
            ? $userPrice->custom_price
            : $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $serviceFee) {

            return redirect()->route('user.ipe')
                ->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            try {

                $url = env('BASE_API_URL_s8v').'/api/clearance';
                $token = env('API_TOKEN_s8v');
                $data = ['tracking_id' => strtoupper($request->input('trackingId')), 'token' => $token];

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                Log::info('IPE Response', $response);

                if (isset($response['success'])) {


                $balance = $wallet->balance - $serviceFee;

                Wallet::where('user_id', $loginUserId)
                    ->update(['balance' => $balance]);

                $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($serviceFee, 2);

                $tnx = $this->transactionService->createTransaction($loginUserId, $serviceFee, 'IPE Request', $serviceDesc, 'Wallet', 'Approved');

                $this->processResponseDataIpe($loginUserId, $tnx->id, $request->input('trackingId'));


                return redirect()->route('user.ipe')
                    ->with('success', 'IPE request has been submitted successfully.');
                } else {
                    return redirect()->route('user.ipe')
                        ->with('error', 'IPE request is not successful, Reason: '.$response['error']);
                }
            } catch (\Exception $e) {
                return redirect()->route('user.ipe')
                    ->with('error', 'An error occurred while making the API request');
            }
        }
    }

    public function ipeBulkRequest(Request $request)
    {
        $request->validate([
            'trackingIds' => 'required|string',
        ]);

        $trackingList = preg_split('/\r\n|\r|\n/', trim($request->trackingIds));
        $trackingList = array_filter($trackingList);

        if (empty($trackingList)) {
            return back()->with('error', 'No Tracking IDs provided.');
        }

        $ServiceFee = Service::where('service_code', '112')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return back()->with('error', 'Bulk IPE disabled.');
        }

        $now = Carbon::now();
        $loginUserId = auth()->id();

        $userPrice = UserServicePrice::where('user_id', $loginUserId)
            ->where('service_id', $ServiceFee->id)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            })
            ->latest()->first();

        $serviceFee = $userPrice ? $userPrice->custom_price : $ServiceFee->amount;

        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance ?? 0;

        $totalCost = $serviceFee * count($trackingList);

        if ($wallet_balance < $totalCost) {
            return back()->with('error', 'Insufficient wallet balance for bulk request.');
        }

        try {
            foreach ($trackingList as $trackingId) {

                $trackingId = strtoupper(trim($trackingId));
                if (strlen($trackingId) !== 15) {
                    continue;
                }

                $url = env('BASE_API_URL_s8v').'/api/clearance';
                $token = env('API_TOKEN_s8v');
                $data = ['tracking_id' => strtoupper($trackingId), 'token' => $token];

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                Log::info('IPE Response', $response);

                $wallet_balance -= $serviceFee;

                Wallet::where('user_id', $loginUserId)->update(['balance' => $wallet_balance]);

                $desc = 'IPE Tracking ID '.$trackingId;

                $transaction = $this->transactionService->createTransaction(
                    $loginUserId,
                    $serviceFee,
                    'IPE Bulk Request',
                    $desc,
                    'Wallet',
                    'Approved'
                );

               $this->processResponseDataIpe($loginUserId,$transaction->id, $trackingId);
            }

            return back()->with('success', 'Bulk IPE request submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong during bulk request.');
        }
    }

    public function ipeRequestStatus($trackingId)
    {
        try {

            $url = env('BASE_API_URL_s8v').'/api/clearance/status';
            $token = env('API_TOKEN_s8v');

            $data = ['tracking_id' => $trackingId, 'token' => $token];

            $headers = [
                'Accept: application/json, text/plain, */*',
                'Content-Type: application/json',
            ];

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute request
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            $response = json_decode($response, true);

            if (isset($response['status']) && $response['status'] === 'Successful') {

                IpeRequest::where('trackingId', $trackingId)
                    ->where('user_id', $this->loginId)
                    ->update([
                        'reply' => ($response['reply'] ?? 'N/A').'<br>'.
                            ($response['nin'] ?? 'N/A').'<br>'.
                            'Name: '.($response['name'] ?? 'N/A').'<br>'.
                            'DOB: '.($response['dob'] ?? 'N/A'),
                        'status' => 'successful',
                    ]);

                return redirect()->route('user.ipe')
                    ->with('success', 'IPE request is successful, check the reply section');
            } elseif (isset($response['status']) && $response['status'] === 'Failed') {

                // process refund & NIN Services Fee
                $ServiceFee = 0;

                $ServiceFee = Service::where('service_code', '112')
                    ->where('status', 'enabled')
                    ->first();

                if (! $ServiceFee) {
                    return redirect()->route('user.ipe')
                        ->with('error', 'Sorry Action not Allowed !');
                }

                $ServiceFee = $ServiceFee->amount;

                $wallet = Wallet::where('user_id', $this->loginId)->first();

                $balance = $wallet->balance + $ServiceFee;

                // Check if already refunded
                $refunded = IpeRequest::where('trackingId', $trackingId)
                    ->where('user_id', $this->loginId)
                    ->whereNull('refunded_at')
                    ->first();

                if ($refunded) {
                    Wallet::where('user_id', $this->loginId)
                        ->update(['balance' => $balance]);

                    IpeRequest::where('trackingId', $trackingId)
                        ->where('user_id', $this->loginId)
                        ->update(['reply' => $response['reply'], 'status' => 'failed']);

                    $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'IPE Refund', "IPE Refund for Tracking ID: {$trackingId}", 'Wallet', 'Approved');
                }

                return redirect()->route('user.ipe')
                    ->with('error', $response['status'].': '.$response['reply']);
            } elseif (isset($response['status']) && $response['status'] === 'New') {
                return redirect()->route('user.ipe')
                    ->with('success', 'Tracking ID has been submitted to get old  Tracking ID, kindly check status below after some few minutes');
            } else {
                return redirect()->route('user.ipe')
                    ->with('error', $response['error'] ?? 'Unexpected error occurred');
            }
        } catch (\Exception $e) {

            return redirect()->route('user.ipe')
                ->with('error', 'An error occurred while making the API request');
        }
    }

    public function bvnRetrieve(Request $request)
    {

        $request->validate(['bvn' => 'required|numeric|digits:11']);

        // BVN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '101')->where('status', 'enabled')->first();
        $ServiceFee = $ServiceFee->amount;

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['bvn' => $request->input('bvn')];

                $url = env('BASE_URL_VERIFY_USER2').'api/v1/verify-bvn';
                $token = env('VERIFY_USER_TOKEN2');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['respCode']) && $response['respCode'] == '000') {

                    $data = $response['data'];

                    $this->processResponseDataForBVN($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'BVN Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif ($response['respCode'] == '99120010') {

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $this->loginId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Verification', $serviceDesc, 'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninTrackRetrieve(Request $request)
    {

        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '108')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['trackingId' => $request->input('trackingId')];

                $url = env('BASE_URL_VERIFY_USER').'api/v1/tracking-nin';
                $token = env('VERIFY_USER_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['respCode']) && $response['respCode'] == '000') {

                    $data = $response['message'];

                    $this->processResponseDataForNINTracking($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Personalize', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif ($response['respCode'] == '103') {

                    // $balance = $wallet->balance - $ServiceFee;

                    // Wallet::where('user_id', $this->loginId)
                    //     ->update(['balance' => $balance]);

                    // $serviceDesc = 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2);

                    // $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN Verification', $serviceDesc,  'Wallet', 'Approved');

                    return response()->json([
                        'status' => 'Not Found',
                        'errors' => ['Succesfully Verified with ( NIN do not exist)'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function processResponseDataForNIN($data)
    {

        Verification::create([
            'idno' => $data['nin'],
            'type' => 'NIN',
            'nin' => $data['nin'],
            'first_name' => $data['firstName'],
            'middle_name' => $data['middleName'],
            'last_name' => $data['surname'],
            'dob' => $data['birthDate'],
            'gender' => $data['gender'],
            'phoneno' => $data['telephoneNo'],
            'photo' => $data['photo'],
        ]);
    }

    public function processResponseDataForBVN($data)
    {
        $user = Verification::create(
            [
                'idno' => $data['bvn'],
                'type' => 'BVN',
                'nin' => '',
                'first_name' => $data['firstName'],
                'middle_name' => $data['middleName'],
                'last_name' => $data['lastName'],
                'phoneno' => $data['phoneNumber'],
                'dob' => $data['birthday'],
                'gender' => $data['gender'],
                'photo' => $data['photo'],
            ]
        );
    }

    // public function processResponseDataForBVNPhone($data)
    // {

    //     try {
    //         $requiredFields = ['bvn', 'firstName', 'lastName', 'dateOfBirth', 'gender', 'photo'];
    //         foreach ($requiredFields as $field) {
    //             if (empty($data[$field])) {
    //                 throw new \Exception("Missing required field: $field");
    //             }
    //         }

    //         $user = Verification::create([
    //             'idno' => $data['bvn'],
    //             'type' => 'BVN',
    //             'nin' => $data['nin'] ?? null,
    //             'email' => $data['email'] ?? null,
    //             'first_name' => $data['firstName'],
    //             'middle_name' => $data['middleName'] ?? null,
    //             'last_name' => $data['lastName'],
    //             'phoneno' => $data['phoneNumber'] ?? null,
    //             'dob' => $this->safeParseDate($data['dateOfBirth'] ?? ''),
    //             'gender' => $data['gender'],
    //             'photo' => $data['photo'],
    //             'enrollment_bank' => $data['enrollmentBank'] ?? null,
    //             'enrollment_branch' => $data['enrollmentBranch'] ?? null,
    //             'registration_date' => $this->safeParseDate($data['registrationDate'] ?? ''),
    //             'title' => $data['title'] ?? null,
    //             'state' => $data['stateOfOrigin'] ?? null,
    //             'lga' => $data['lgaOfOrigin'] ?? null,
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Verification creation failed', [
    //             'message' => $e->getMessage(),
    //             'data' => $data,
    //         ]);
    //     }
    // }

    public function safeParseDate($value)
    {
        $formats = ['d-M-Y', 'Y-m-d', 'd/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, trim($value))->format('Y/m/d');
            } catch (\Exception $e) {
                continue;
            }
        }

        Log::warning("Failed to parse date: {$value}", ['formats_tried' => $formats]);

        return null;
    }

    public function processResponseDataForNINTracking($data)
    {

        $user = Verification::create([
            'idno' => $data['nin'],
            'type' => 'NIN',
            'nin' => $data['nin'],
            'trackingId' => $data['trackingid'],
            'first_name' => $data['firstname'],
            'middle_name' => $data['middlename'],
            'last_name' => $data['lastname'],
            'dob' => '1970-01-01',
            'gender' => $data['gender'] == 'm' || $data['gender'] == 'Male' ? 'Male' : 'Female',
            'state' => $data['state'],
            'lga' => $data['town'],
            'address' => $data['address'],
            'photo' => $data['face'],
        ]);
    }

    public function processResponseDataForNINPhone($data)
    {

        $dob = $this->cleanDob($data['birthdate'] ?? null);

        try {
            $user = Verification::create([
                'user_id' => auth()->user()->id,
                'idno' => $data['idNumber'] ?? ($data['nin'] ?? null),
                'type' => 'NIN',
                'nin' => $data['idNumber'] ?? ($data['nin'] ?? null),
                'trackingId' => $data['trackingId'] ?? null,
                'first_name' => $data['firstName'] ?? ($data['firstname'] ?? null),
                'middle_name' => $data['middleName'] ?? ($data['middlename'] ?? null),
                'last_name' => $data['lastName'] ?? ($data['surname'] ?? null),
                'phoneno' => $data['mobile'] ?? ($data['telephoneno'] ?? null),
                'dob' => $dob,
                'gender' => isset($data['gender']) && (strtolower($data['gender']) == 'm' || strtolower($data['gender']) == 'male') ? 'Male' : 'Female',
                'state' => $data['address']['state'] ?? ($data['self_origin_state'] ?? null),
                'lga' => $data['address']['lga'] ?? ($data['self_origin_lga'] ?? null),
                'address' => $data['address']['addressLine'] ?? ($data['residence_AdressLine1'] ?? null),
                'photo' => $data['photo'] ?? ($data['image'] ?? null),
                'town' => $data['address']['town'] ?? ($data['self_origin_place'] ?? null),
                'signature' => $data['signature'] ?? null,
                'residence_state' => $data['address']['state'] ?? ($data['residence_state'] ?? null),
                'residence_lga' => $data['address']['lga'] ?? ($data['residence_lga'] ?? null),
            ]);
        } catch (\Exception $e) {

            Log::error('Verification creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create verification record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function cleanDob(?string $dob): ?string
    {
        // If dob is missing or masked, use default fallback date
        if (! $dob || str_contains($dob, '*')) {
            return '1990-01-01';
        }

        try {
            return \Carbon\Carbon::createFromFormat('d-m-Y', $dob)->format('Y-m-d');
        } catch (\Exception $e) {
            // If parsing fails, fallback as well
            return '1990-01-01';
        }
    }

    public function processResponseDataIpe($userId, $trxId, $trackingNo)
    {
        try {
            IpeRequest::create([
                'user_id' => $userId,
                'tnx_id'=>$trxId,
                'trackingId' => $trackingNo,
            ]);
        } catch (\Exception $e) {

            Log::error('Request creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create Ipe Request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function regularSlip($nin_no)
    {

        // NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '105')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Regular NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->regularPDF($nin_no);

            return $response;
        }
    }

    public function standardSlip($nin_no)
    {

        // NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '106')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Standard NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->standardPDF($nin_no);

            return $response;
        }
    }

    public function premiumSlip($nin_no)
    {
        // NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '107')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Premium NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->premiumPDF($nin_no);

            return $response;
        }
    }

    public function premiumBVN($bvnno)
    {

        // BVN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '103')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Premium BVN Slip', $serviceDesc, 'Wallet', 'Approved');

            if (Verification::where('idno', $bvnno)->exists()) {

                $veridiedRecord = Verification::where('idno', $bvnno)
                    ->latest()
                    ->first();

                $data = $veridiedRecord;
                $view = view('verification.PremiumBVN', compact('veridiedRecord'))->render();

                return response()->json(['view' => $view]);
            } else {

                return response()->json([
                    'message' => 'Error',
                    'errors' => ['Not Found' => 'Verification record not found !'],
                ], 422);
            }
        }
    }

    public function standardBVN($bvnno)
    {

        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '102')->first();
        $ServiceFee = $ServiceFee->amount;

        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Standard BVN Slip', $serviceDesc, 'Wallet', 'Approved');

            if (Verification::where('idno', $bvnno)->exists()) {

                $veridiedRecord = Verification::where('idno', $bvnno)
                    ->latest()
                    ->first();

                $data = $veridiedRecord;
                $view = view('verification.freeBVN', compact('veridiedRecord'))->render();

                return response()->json(['view' => $view]);
            } else {

                return response()->json([
                    'message' => 'Error',
                    'errors' => ['Not Found' => 'Verification record not found !'],
                ], 422);
            }
        }
    }

    public function plasticBVN($bvnno)
    {
        // Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '109')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Plastic ID Card', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new BVN_PDF_Repository;
            $response = $repObj->plasticPDF($bvnno);

            return $response;
        }
    }

    public function bvnPhoneSearch()
    {
        $serviceCodes = ['115'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        $ServiceFee = $services->get('115') ?? 0.00;

        $query = BvnPhoneSearch::where('user_id', $this->loginId);

        if (request()->has('filter_phone') && request('filter_phone') != '') {
            $query->where('phone_number', 'like', '%'.request('filter_phone').'%');
        }

        if (request()->has('filter_ref') && request('filter_ref') != '') {
            $query->where('refno', 'like', '%'.request('filter_ref').'%');
        }

        if (request()->has('filter_status') && request('filter_status') != '') {
            $query->where('status', request('filter_status'));
        }

        if (request()->has('filter_date') && request('filter_date') != '') {
            $query->whereDate('created_at', request('filter_date'));
        }

        $bvns = $query->orderBy('id', 'desc')->paginate(5);

        return view('verification.phone-search', compact('ServiceFee', 'bvns'));
    }

    public function bvnPhoneRequest(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits:11',
        ]);

        $service = Service::where('service_code', '115')
            ->where('status', 'enabled')
            ->first();

        if (! $service) {
            return redirect()->route('user.bvn-phone-search')
                ->with('error', 'Sorry, action not allowed!');
        }

        $serviceFee = (float) $service->amount;

        $loginUserId = auth()->id();
        $wallet = Wallet::where('user_id', $loginUserId)->first();

        if (! $wallet) {
            return redirect()->route('user.bvn-phone-search')
                ->with('error', 'Wallet not found!');
        }

        if ($wallet->balance < $serviceFee) {
            return redirect()->route('user.bvn-phone-search')
                ->with('error', 'Sorry, wallet not sufficient for transaction!');
        }

        $apiData = [
            'phone_number' => $request->phone_number,
        ];

        try {
            $url = rtrim(env('BASE_URL_VERIFY_USER2'), '/').'/api/v1/bvn/search';
            $token = env('VERIFY_USER_TOKEN2');

            $headers = [
                'Accept: application/json',
                'Content-Type: application/json',
                "Authorization: Bearer {$token}",
            ];

            // Initialize cURL
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_POSTFIELDS => json_encode($apiData),
                CURLOPT_TIMEOUT => 60,
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception('cURL Error: '.curl_error($ch));
            }

            curl_close($ch);

            $responseData = json_decode($response, true);

            Log::info('BVN Phone Search Response', ['response' => $responseData]);

            if (isset($responseData['success']) && $responseData['success'] === true) {
                $refno = $responseData['data']['refno'] ?? null;

                $newBalance = $wallet->balance - $serviceFee;
                $wallet->update(['balance' => $newBalance]);

                $serviceDesc = 'Wallet debited with a service fee of ₦'.number_format($serviceFee, 2);
                $trx = $this->transactionService->createTransaction(
                    $loginUserId,
                    $serviceFee,
                    'BVN Phone Search',
                    $serviceDesc,
                    'Wallet',
                    'Approved'
                );

                BvnPhoneSearch::create([
                    'user_id' => $loginUserId,
                    'tnx_id' => $trx->id ?? null,
                    'refno' => $refno,
                    'phone_number' => $request->phone_number,
                    'name' => 'API',
                ]);

                return redirect()->back()->with('success', $responseData['message'] ?? 'BVN search submitted successfully!');
            }

            if (isset($responseData['success']) && $responseData['success'] === false) {
                return redirect()->back()->with('error', '(code: 01) BVN search failed!');
            }

            return redirect()->back()->with('error', 'Unexpected API response. Please try again.');
        } catch (\Exception $e) {
            Log::error('BVN Phone Search Error', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An error occurred while making the API request. Please try again.');
        }
    }

    public function processResponseDataForNINDEMO($data)
    {

        try {
            Verification::create([
                'user_id' => auth()->user()->id,
                'idno' => $data['idNumber'],
                'type' => 'NIN',
                'nin' => $data['idNumber'],
                'trackingId' => $data['trackingId'],
                'first_name' => $data['firstName'],
                'middle_name' => $data['middleName'],
                'last_name' => $data['lastName'],
                'phoneno' => $data['mobile'],
                'dob' => \Carbon\Carbon::createFromFormat('d-m-Y', $data['dateOfBirth'])->format('Y-m-d'),
                'gender' => $data['gender'] == 'm' || $data['gender'] == 'Male' ? 'Male' : 'Female',
                'state' => $data['self_origin_state'],
                'lga' => $data['self_origin_lga'],
                'town' => $data['self_origin_place'],
                'address' => $data['addressLine'],
                'photo' => $data['photo'],
                'signature' => $data['signature'],
                'residence_state' => $data['residence_state'],
                'residence_lga' => $data['residence_lga'],

            ]);
        } catch (\Exception $e) {

            Log::error('Verification creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create verification record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

      public function processResponseDataForNINV5DEMO($data)
    {

        try {
            Verification::create([
                'user_id' => auth()->user()->id,
                'idno' => $data['nin'],
                'type' => 'NIN',
                'nin' => $data['nin'],
                'trackingId' => $data['trackingId'],
                'first_name' => $data['firstname'],
                'middle_name' => $data['middlename'],
                'last_name' => $data['surname'],
                'phoneno' => $data['telephoneno'],
                'dob' => \Carbon\Carbon::createFromFormat('d-m-Y', $data['birthdate'])->format('Y-m-d'),
                'gender' => $data['gender'] == 'm' || $data['gender'] == 'Male' ? 'Male' : 'Female',
                'state' => $data['self_origin_state'],
                'lga' => $data['self_origin_lga'],
                'town' => $data['self_origin_place'],
                'address' => $data['residence_AdressLine1'],
                'photo' => $data['photo']??  $data['image']?? $data['photo']?? null,
                'signature' => $data['signature'],
                'residence_state' => $data['residence_state'],
                'residence_lga' => $data['residence_lga'],

            ]);
        } catch (\Exception $e) {

            Log::error('Verification creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create verification record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function basicSlip($nin_no)
    {
        // NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '117')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Basic NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->basicPDF($nin_no);

            return $response;
        }
    }

    public function bvnPhoneVerify()
    {
        // Fetch all required service fees in one query
        $serviceCodes = ['124', '102', '103', '109'];
        $services = Service::whereIn('service_code', $serviceCodes)->get()->keyBy('service_code');

        $BVNFee = $services->get('124') ?? 0.00;
        $bvn_standard_fee = $services->get('102') ?? 0.00;
        $bvn_premium_fee = $services->get('103') ?? 0.00;
        $bvn_plastic_fee = $services->get('109') ?? 0.00;

        return view('verification.bvn-phone-verify', compact('BVNFee', 'bvn_standard_fee', 'bvn_premium_fee', 'bvn_plastic_fee'));
    }

    public function bvnPhoneRetrieve(Request $request)
    {

        $request->validate(['phoneNumber' => 'required|numeric|digits:11']);

        // BVN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '124')->where('status', 'enabled')->first();
        $ServiceFee = $ServiceFee->amount;

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {

            try {

                $data = ['phone' => $request->input('phoneNumber')];

                $url = env('BASE_URL_VERIFY_USER').'api/v1/bvn/verify-phone';
                $token = env('VERIFY_USER_TOKEN');

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                    "Authorization: Bearer $token",
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                if (isset($response['response_code']) && $response['response_code'] === '00') {

                    $data = $response['data'];

                    // Log::info('Verification record:', ['data' => $data]);

                    // $this->processResponseDataForBVNPhone($data);

                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'BVN Phone Verification', $serviceDesc, 'Wallet', 'Approved');

                    return json_encode(['status' => 'success', 'data' => $data]);
                } elseif (isset($response['response_code']) && $response['response_code'] === '01') {

                    return response()->json([
                        'status' => 'Record Not Found',
                        'errors' => ['Succesfully Verified with no record found'],
                    ], 422);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function ninv3Retrieve(Request $request)
    {

        $request->validate(
            ['nin' => 'required|numeric|digits:11'],
            [
                'nin.required' => 'The NIN number is required.',
                'nin.numeric' => 'The NIN number must be a numeric value.',
                'nin.digits' => 'The NIN must be exactly 11 digits.',
            ]
        );

        // NIN Services Fee
        $ServiceFee = 0;

        $ServiceFee = Service::where('service_code', '130')
            ->where('status', 'enabled')
            ->first();

        if (! $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Service Error' => 'Sorry Action not Allowed !'],
            ], 422);
        }

        $ServiceFee = $ServiceFee->amount;

        $loginUserId = auth()->user()->id;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $loginUserId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            try {

                $url = env('BASE_API_URL_s8v').'/api/verification';
                $token = env('API_TOKEN_s8v');
                $data = ['nin' => $request->input('nin'), 'token' => $token];

                $headers = [
                    'Accept: application/json, text/plain, */*',
                    'Content-Type: application/json',
                ];

                // Initialize cURL
                $ch = curl_init();

                // Set cURL options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                // Execute request
                $response = curl_exec($ch);

                // Check for cURL errors
                if (curl_errno($ch)) {
                    throw new \Exception('cURL Error: '.curl_error($ch));
                }

                // Close cURL session
                curl_close($ch);

                $response = json_decode($response, true);

                Log::info('V3 Response', $response);

                if (isset($response['status']) && $response['status'] === 'Successful') {
                    $data = $response['data'];
                    $balance = $wallet->balance - $ServiceFee;

                    Wallet::where('user_id', $loginUserId)
                        ->update(['balance' => $balance]);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

                    $transaction = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'NIN V3 Request', $serviceDesc, 'Wallet', 'Approved');

                    $this->processResponseDataForV3($data);

                    return json_encode(['status' => 'success', 'data' => $data]);
                } else {
                    return response()->json([
                        'status' => 'Verification Failed',
                        'errors' => ['Verification Failed: No need to worry, your wallet remains secure and intact. Please try again or contact support for assistance.'],
                    ], 422);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'Request failed',
                    'errors' => ['An error occurred while making the API request'],
                ], 422);
            }
        }
    }

    public function processResponseDataForV3($data)
    {
        try {
            Verification::create([
                'user_id' => auth()->user()->id,
                'idno' => $data['nin'],
                'type' => 'NIN',
                'nin' => $data['nin'],
                'trackingId' => $data['tracking_id'],
                'first_name' => $data['firstName'],
                'middle_name' => $data['middleName'],
                'last_name' => $data['lastName'],
                'phoneno' => $data['mobile'],
                'dob' => $data['dateOfBirth'],
                'gender' => $data['gender'],
                'address' => $data['addressLine'],
                // 'photo' => $data['photo'],
                'photo' => $data['photo'] = preg_replace('/^data:image\/[^;]+;base64,/', '', $data['photo']),
                'signature' => $data['signature'],
                'residence_state' => $data['birthstate'],
                'residence_lga' => $data['birthLGA'],

            ]);
        } catch (\Exception $e) {

            Log::error('Verification creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create verification record.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function processPersonalizeRequest($userId, $trackingNo, $refno, $trx_id, $tag = 'auto')
    {
        try {
            if ($tag == 'Manual') {
                PersonalizeRequest::create([
                    'user_id' => $userId,
                    'refno' => $refno,
                    'tnx_id' => $trx_id,
                    'tracking_no' => $trackingNo,
                    'tag' => 'Manual',
                ]);
            } else {

                PersonalizeRequest::create([
                    'user_id' => $userId,
                    'refno' => $refno,
                    'tnx_id' => $trx_id,
                    'tracking_no' => $trackingNo,
                ]);
            }
        } catch (\Exception $e) {

            Log::error('Request creation failed: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to create Personalize Request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function ninVerify3(Request $request)
    {

        $serviceCodes = ['130', '105', '106', '107', '117'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        // Extract specific service fees
        $ServiceFee = $services->get('130') ?? 0.00;
        $standard_nin_fee = $services->get('106') ?? 0.00;
        $regular_nin_fee = $services->get('105') ?? 0.00;
        $premium_nin_fee = $services->get('107') ?? 0.00;
        $basic_nin_fee = $services->get('117') ?? 0.00;

        return view('verification.nin-verifyv3', compact(
            'ServiceFee',
            'regular_nin_fee',
            'standard_nin_fee',
            'premium_nin_fee',
            'basic_nin_fee'
        ));
    }

    public function tinSlip($tin_no, $entity)
    {
         // NIN Services Fee
        $ServiceFee = 0;
        $ServiceFee = Service::where('service_code', '148')->first();
        $ServiceFee = $ServiceFee->amount;

        // Check if wallet is funded
        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return response()->json([
                'message' => 'Error',
                'errors' => ['Wallet Error' => 'Sorry Wallet Not Sufficient for Transaction !'],
            ], 422);
        } else {
            $balance = $wallet->balance - $ServiceFee;

            $affected = Wallet::where('user_id', $this->loginId)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Basic NIN Slip', $serviceDesc, 'Wallet', 'Approved');

            if($entity == 'corporate') {
                $repObj = new NIN_PDF_Repository;
                $response = $repObj->coperateSlip($tin_no);
                //
            }else{

            // Generate PDF
            $repObj = new NIN_PDF_Repository;
            $response = $repObj->individualSlip($tin_no);

            }

            return $response;
        }
    }

    public function showIpeV3(Request $request)
    {
        $serviceCodes = ['112'];
        $services = Service::whereIn('service_code', $serviceCodes)
            ->get()
            ->keyBy('service_code');

        $ServiceFee = $services->get('112') ?? (object) ['amount' => 0.00];

        $query = IpeRequest::where('user_id', auth()->user()->id)->where('tag', 'IPE_V3');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('trackingId', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $ipes = $query->orderBy('id', 'desc')->paginate(10);

        $statusCounts = IpeRequest::selectRaw('status, COUNT(*) as count')
            ->where('user_id', auth()->user()->id)
            ->where('tag', 'IPE_V3')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAll = IpeRequest::where('user_id', auth()->id())->where('tag', 'IPE_V3')->count();
        $totalPending = $statusCounts['pending'] ?? 0;
        $totalFailed = $statusCounts['failed'] ?? 0;
        $totalSuccessful = $statusCounts['successful'] ?? 0;
        $totalProcessing = $statusCounts['processing'] ?? 0;

        return view('verification.ipe3', compact(
            'ServiceFee',
            'ipes',
            'totalPending',
            'totalFailed',
            'totalSuccessful',
            'totalProcessing',
            'totalAll'
        ));
    }
      public function ipeRequestV3(Request $request)
    {
        $request->validate([
            'trackingId' => 'required|alpha_num|size:15',
        ]);

        $ServiceFeeModel = Service::where('service_code', '112')->where('status', 'enabled')->first();
        if (!$ServiceFeeModel) {
            return redirect()->route('user.ipe.v3')->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $ServiceFeeModel->amount;
        $loginUserId = auth()->user()->id;

        $pendingRequest = IpeRequest::where('trackingId', strtoupper($request->trackingId))
            ->whereIn('status', ['pending', 'processing'])
            ->where('tag', 'IPE_V3')
            ->exists();

        if ($pendingRequest) {
            return redirect()->route('user.ipe.v3')->with('error', 'Sorry, you already have a pending request with that tracking ID!');
        }

        $wallet = Wallet::where('user_id', $loginUserId)->first();
        if ($wallet->balance < $ServiceFee) {
            return redirect()->route('user.ipe.v3')->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        }

        try {
            $url = env('BASE_URL_VERIFY_USER2') . 'api/v1/ipe';
            $token = env('VERIFY_USER_TOKEN2');
            $data = ['trackingId' => strtoupper($request->input('trackingId'))];

            $response = Http::withToken($token)->timeout(40)->post($url, $data);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['status']) && $responseData['status'] === true) {


                    $wallet->decrement('balance', $ServiceFee);

                    $serviceDesc = 'Wallet debitted with a service fee of ₦' . number_format($ServiceFee, 2);
                    $trx_id = $this->transactionService->createTransaction($loginUserId, $ServiceFee, 'IPE Clearance Request V2', $serviceDesc, 'Wallet', 'Approved');

                     IpeRequest::create([
                        'user_id' => $loginUserId,
                        'trackingId' => strtoupper($request->input('trackingId')),
                        'tag' => 'IPE_V3',
                        'tnx_id' => $trx_id->id,
                        'status' => 'pending',
                        'resp_code' => $responseData['data']['resp_code'] ?? '100',
                    ]);

                    return redirect()->route('user.ipe.v3')->with('success', 'IPE Clearance V2 request submitted successfully.');
                }
            }
            return redirect()->route('user.ipe.v3')->with('error', 'IPE request failed: ' . ($response->json()['message'] ?? 'Unable to connect to service provider.'));
        } catch (\Exception $e) {
            Log::error('IPE V2 Error: ' . $e->getMessage());
            return redirect()->route('user.ipe.v3')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

}
