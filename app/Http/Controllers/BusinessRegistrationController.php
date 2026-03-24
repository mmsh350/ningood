<?php

namespace App\Http\Controllers;

use App\Models\BusinessRegistration;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BusinessRegistrationController extends Controller
{
    protected $transactionService;

    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
        $this->loginId = Auth::id();
    }

    public function create()
    {

        $service = Service::where('service_code', '146')
            ->where('status', 'enabled')
            ->first();

        $ServiceFee = $service->amount ?? 0.00;

        $submissions = BusinessRegistration::where('user_id', $this->loginId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('business.create', compact('submissions', 'ServiceFee'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'res_state' => 'required|string|max:255',
            'res_lga' => 'required|string|max:255',
            'res_city' => 'required|string|max:255',
            'res_house_number' => 'required|string|max:50',
            'res_street_name' => 'required|string|max:255',
            'res_description' => 'required|string',
            'bus_state' => 'required|string|max:255',
            'bus_lga' => 'required|string|max:255',
            'bus_city' => 'required|string|max:255',
            'bus_house_number' => 'required|string|max:50',
            'bus_street_name' => 'required|string|max:255',
            'bus_description' => 'required|string',
            'nature_of_business' => 'required|string|max:255',
            'business_name_1' => 'required|string|max:255',
            'business_name_2' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nin' => 'required|file|mimes:pdf,jpg,png',
            'signature' => 'required|file|mimes:jpg,png',
            'passport' => 'required|file|mimes:jpg,png',
        ], [
            'nin.required' => 'Please upload your NIN document.',
            'nin.file' => 'The NIN must be a valid file.',
            'nin.mimes' => 'The NIN must be a file of type: pdf, jpg, png.',

            'signature.required' => 'Please upload your signature.',
            'signature.file' => 'The signature must be a valid file.',
            'signature.mimes' => 'The signature must be a file of type: jpg, png.',

            'passport.required' => 'Please upload your passport photograph.',
            'passport.file' => 'The passport must be a valid file.',
            'passport.mimes' => 'The passport must be a file of type: jpg, png.',
        ]);

        unset($data['nin'], $data['signature'], $data['passport']);

        $ServiceFee = 0;

        $Service = Service::where('service_code', '146')
            ->where('status', 'enabled')
            ->first();

        if (! $Service) {
            return redirect()->back()->with('error', 'Sorry Action not Allowed !');
        }

        $ServiceFee = $Service->amount;

        $wallet = Wallet::where('user_id', $this->loginId)->first();
        $wallet_balance = $wallet->balance;
        $balance = 0;

        if ($wallet_balance < $ServiceFee) {
            return redirect()->back()->with('error', 'Sorry Wallet Not Sufficient for Transaction !');
        } else {

            $data['user_id'] = $this->loginId;

            // foreach (['nin', 'signature', 'passport'] as $fileField) {
            //     if ($request->hasFile($fileField)) {
            //         $data[$fileField . '_path'] =
            //             $request->file($fileField)->store("documents/{$fileField}", 'public');
            //     }
            // }
            foreach (['nin', 'signature', 'passport'] as $fileField) {
                if ($request->hasFile($fileField)) {
                    $file = $request->file($fileField);
                    $extension = $file->getClientOriginalExtension(); // preserve extension
                    $filename = $fileField.'_'.time().'.'.$extension; // e.g., nin_1700000000.pdf
                    $data[$fileField.'_path'] = $file->storeAs("documents/{$fileField}", $filename, 'public');
                }
            }

            $serviceDesc = 'Wallet debitted with a service fee of ₦'.number_format($ServiceFee, 2);

            $transaction = $this->transactionService->createTransaction($this->loginId, $ServiceFee, 'Business Name Reg (CAC) ', $serviceDesc, 'Wallet', 'Approved');

            $data['tnx_id'] = $transaction->id;
            $data['refno'] = $transaction->referenceId;

            BusinessRegistration::create($data);

            return redirect()->back()->with('success', 'Business registration submitted successfully.');
        }
    }

    public function edit($id)
    {
        $registration = BusinessRegistration::where('user_id', $this->loginId)->findOrFail($id);

        if ($registration->status !== 'query') {
            return redirect()->route('user.business.create')->with('error', 'Only queried requests can be edited.');
        }

        $service = Service::where('service_code', '146')
            ->where('status', 'enabled')
            ->first();

        $ServiceFee = $service->amount ?? 0.00;

        return view('business.edit', compact('registration', 'ServiceFee'));
    }

    public function update(Request $request, $id)
    {
        $registration = BusinessRegistration::where('user_id', $this->loginId)->findOrFail($id);

        if ($registration->status !== 'query') {
            return redirect()->route('user.business.create')->with('error', 'Only queried requests can be edited.');
        }

        $data = $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_name' => 'nullable|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'phone_number' => 'required|string|max:20',
            'res_state' => 'required|string|max:255',
            'res_lga' => 'required|string|max:255',
            'res_city' => 'required|string|max:255',
            'res_house_number' => 'required|string|max:50',
            'res_street_name' => 'required|string|max:255',
            'res_description' => 'required|string',
            'bus_state' => 'required|string|max:255',
            'bus_lga' => 'required|string|max:255',
            'bus_city' => 'required|string|max:255',
            'bus_house_number' => 'required|string|max:50',
            'bus_street_name' => 'required|string|max:255',
            'bus_description' => 'required|string',
            'nature_of_business' => 'required|string|max:255',
            'business_name_1' => 'required|string|max:255',
            'business_name_2' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nin' => 'nullable|file|mimes:pdf,jpg,png',
            'signature' => 'nullable|file|mimes:jpg,png',
            'passport' => 'nullable|file|mimes:jpg,png',
        ]);

        unset($data['nin'], $data['signature'], $data['passport']);

        foreach (['nin', 'signature', 'passport'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $file = $request->file($fileField);
                $extension = $file->getClientOriginalExtension();
                $filename = $fileField . '_' . date('YmdHis') . '.' . $extension;
                $data[$fileField . '_path'] = $file->storeAs("documents/{$fileField}", $filename, 'public');
            }
        }

        $data['status'] = 'pending'; // Reset to pending after update
        $registration->update($data);

        return redirect()->route('user.business.create')->with('success', 'Registration updated and resubmitted successfully.');
    }

    public function index(Request $request)
    {

        $pending = BusinessRegistration::whereIn('status', ['pending', 'processing'])
            ->count();

        $resolved = BusinessRegistration::where('status', 'completed')
            ->count();

        $rejected = BusinessRegistration::where('status', 'failed')
            ->count();

        $queried = BusinessRegistration::where('status', 'query')
            ->count();

        $total_request = BusinessRegistration::count();

        $query = BusinessRegistration::with(['user', 'transactions']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('refno', 'like', "%{$searchTerm}%")
                    ->orWhere('surname', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('status', 'like', "%{$searchTerm}%")
                    ->orWhereHas('user', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        if ($dateFrom = request('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $registrationList = $query
            ->orderByRaw("
                CASE
                    WHEN status = 'submitted' THEN 1
                    WHEN status = 'processing' THEN 2
                    ELSE 3
                END
            ")
            ->orderByDesc('id')
            ->paginate(10);

        $request_type = 'biz';

        return view('admin.biz-list', compact(
            'pending',
            'resolved',
            'rejected',
            'queried',
            'total_request',
            'registrationList',
            'request_type'
        ));
    }

    public function showRequests($request_id, $type, $requests = null)
    {

        $requests = BusinessRegistration::with(['user', 'transactions'])->findOrFail($request_id);
        $request_type = 'biz';

        if (strtolower($requests->status) == 'failed') {
            abort(404, 'Kindly Submit a new request');
        }

        return view(
            'admin.view-request3',
            compact(
                'requests',
                'request_type'
            )
        );
    }

    public function updateRequestStatus(Request $request, $id, $type)
    {

        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,query',
            'comment' => 'required|string',
            'document.*' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $requestDetails = BusinessRegistration::findOrFail($id);

        $disk = Storage::disk('public');

        if ($request->has('delete_old_docs') && $requestDetails->response_documents) {
            collect(json_decode($requestDetails->response_documents, true))
                ->each(fn ($oldDoc) => $disk->exists($oldDoc) ? $disk->delete($oldDoc) : null);
            $requestDetails->response_documents = null;
        }

        if ($request->hasFile('document')) {

            $newDocs = [];
            $existingDocs = json_decode($requestDetails->response_documents ?? '[]', true);

            foreach ($request->file('document') as $index => $doc) {
                $extension = $doc->getClientOriginalExtension();
                $number = str_pad(count($existingDocs) + $index + 1, 4, '0', STR_PAD_LEFT);
                $filename = "doc{$number}.{$extension}";
                $path = $doc->storeAs('docs', $filename, 'public');
                $newDocs[] = $path;
            }

            $requestDetails->response_documents = json_encode(array_merge($existingDocs, $newDocs));
        }

        $route = 'admin.business-reg';
        $status = $request->status;

        $requestDetails->status = $status;
        $requestDetails->response = $request->comment;

        if ($request->status === 'failed') {

            $requestDetails->refunded_at = Carbon::now();

            $refundAmount = $request->refundAmount;

            $wallet = Wallet::where('user_id', $requestDetails->user_id)->first();

            $balance = $wallet->balance + $refundAmount;

            Wallet::where('user_id', $requestDetails->user_id)
                ->update(['balance' => $balance]);

            $serviceDesc = 'Wallet credited with a Request fee of ₦'.number_format($refundAmount, 2);

            $this->transactionService->createTransaction($requestDetails->user_id, $refundAmount, 'Business Name (CAC) Refund', $serviceDesc, 'Wallet', 'Approved');
        }

        $requestDetails->save();

        return redirect()->route($route)->with('success', 'Request status updated successfully.');
    }
}
