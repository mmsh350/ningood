<?php

namespace App\Http\Controllers;

use App\Models\CompanyRegistration;
use App\Models\Service;
use App\Models\Wallet;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyRegistrationController extends Controller
{
    protected $transactionService;
    protected $loginId;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    private function setLoginId()
    {
        $this->loginId = Auth::id();
    }

    public function create()
    {
        $this->setLoginId();
        $service = Service::where('service_code', '151')
            ->where('status', 'enabled')
            ->first();

        $ServiceFee = $service->amount ?? 0.00;

        $submissions = CompanyRegistration::where('user_id', $this->loginId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('cac.create', compact('submissions', 'ServiceFee'));
    }

    public function store(Request $request)
    {
        $this->setLoginId();
        $data = $request->validate([
            'director_surname' => 'required|string|max:255',
            'director_firstname' => 'required|string|max:255',
            'director_othername' => 'nullable|string|max:255',
            'director_dob' => 'required|date',
            'director_gender' => 'required|string',
            'director_email' => 'required|email|max:255',
            'director_phone' => 'required|string|max:20',
            'director_nin' => 'required|string|max:20',

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

            'nature_of_business' => 'required|string',
            'business_name_1' => 'required|string|max:255',
            'business_name_2' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',

            'witness_surname' => 'required|string|max:255',
            'witness_firstname' => 'required|string|max:255',
            'witness_othername' => 'nullable|string|max:255',
            'witness_phone' => 'required|string|max:20',
            'witness_email' => 'required|email|max:255',
            'witness_nin' => 'required|string|max:20',
            'witness_address' => 'required|string',

            'shareholder_surname' => 'required|string|max:255',
            'shareholder_firstname' => 'required|string|max:255',
            'shareholder_othername' => 'nullable|string|max:255',
            'shareholder_dob' => 'required|date',
            'shareholder_gender' => 'required|string',
            'shareholder_nationality' => 'required|string|max:255',
            'shareholder_phone' => 'required|string|max:20',
            'shareholder_email' => 'required|email|max:255',
            'shareholder_nin' => 'required|string|max:20',
            'shareholder_address' => 'required|string',

            'director_signature' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'witness_signature' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'shareholder_signature' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $service = Service::where('service_code', '151')
            ->where('status', 'enabled')
            ->first();

        if (!$service) {
            return redirect()->back()->with('error', 'Service not available at the moment.');
        }

        $serviceFee = $service->amount;
        $wallet = Wallet::where('user_id', $this->loginId)->first();

        if ($wallet->balance < $serviceFee) {
            return redirect()->back()->with('error', 'Insufficient wallet balance.');
        }

        foreach (['director_signature', 'witness_signature', 'shareholder_signature'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $file = $request->file($fileField);
                $filename = $fileField . '_' . time() . '.' . $file->getClientOriginalExtension();
                $data[$fileField . '_path'] = $file->storeAs('documents/company_reg', $filename, 'public');
            }
        }

        $transaction = $this->transactionService->createTransaction(
            $this->loginId,
            $serviceFee,
            'Company Registration',
            'Company Registration Service Fee',
            'Wallet',
            'Approved'
        );

        $data['user_id'] = $this->loginId;
        $data['tnx_id'] = $transaction->id;
        $data['refno'] = $transaction->referenceId;
        $data['status'] = 'pending';

        CompanyRegistration::create($data);

        return redirect()->back()->with('success', 'Registration submitted successfully.');
    }

    public function edit($id)
    {
        $this->setLoginId();
        $registration = CompanyRegistration::where('user_id', $this->loginId)->findOrFail($id);

        if ($registration->status !== 'query') {
            return redirect()->route('user.company.create')->with('error', 'Only queried requests can be edited.');
        }

        $service = Service::where('service_code', '151')
            ->where('status', 'enabled')
            ->first();

        $ServiceFee = $service->amount ?? 0.00;

        return view('cac.edit', compact('registration', 'ServiceFee'));
    }

    public function update(Request $request, $id)
    {
        $this->setLoginId();
        $registration = CompanyRegistration::where('user_id', $this->loginId)->findOrFail($id);

        if ($registration->status !== 'query') {
            return redirect()->route('user.company.create')->with('error', 'Only queried requests can be edited.');
        }

        $data = $request->validate([
            'director_surname' => 'required|string|max:255',
            'director_firstname' => 'required|string|max:255',
            'director_othername' => 'nullable|string|max:255',
            'director_dob' => 'required|date',
            'director_gender' => 'required|string',
            'director_email' => 'required|email|max:255',
            'director_phone' => 'required|string|max:20',
            'director_nin' => 'required|string|max:20',

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

            'nature_of_business' => 'required|string',
            'business_name_1' => 'required|string|max:255',
            'business_name_2' => 'required|string|max:255',
            'business_email' => 'required|email|max:255',

            'witness_surname' => 'required|string|max:255',
            'witness_firstname' => 'required|string|max:255',
            'witness_othername' => 'nullable|string|max:255',
            'witness_phone' => 'required|string|max:20',
            'witness_email' => 'required|email|max:255',
            'witness_nin' => 'required|string|max:20',
            'witness_address' => 'required|string',

            'shareholder_surname' => 'required|string|max:255',
            'shareholder_firstname' => 'required|string|max:255',
            'shareholder_othername' => 'nullable|string|max:255',
            'shareholder_dob' => 'required|date',
            'shareholder_gender' => 'required|string',
            'shareholder_nationality' => 'required|string|max:255',
            'shareholder_phone' => 'required|string|max:20',
            'shareholder_email' => 'required|email|max:255',
            'shareholder_nin' => 'required|string|max:20',
            'shareholder_address' => 'required|string',

            'director_signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'witness_signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'shareholder_signature' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        foreach (['director_signature', 'witness_signature', 'shareholder_signature'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $file = $request->file($fileField);
                $filename = $fileField . '_' . time() . '.' . $file->getClientOriginalExtension();
                $data[$fileField . '_path'] = $file->storeAs('documents/company_reg', $filename, 'public');
            }
        }

        $data['status'] = 'pending'; // Reset status after update
        $registration->update($data);

        return redirect()->route('user.company.create')->with('success', 'Registration updated and resubmitted successfully.');
    }

    public function index(Request $request)
    {
        $query = CompanyRegistration::with(['user', 'transaction']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('refno', 'like', "%$search%")
                  ->orWhere('director_surname', 'like', "%$search%")
                  ->orWhere('business_name_1', 'like', "%$search%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'like', "%$search%");
                  });
            });
        }

        $registrationList = $query->orderBy('created_at', 'desc')->paginate(10);

        $pending = CompanyRegistration::where('status', 'pending')->count();
        $processing = CompanyRegistration::where('status', 'processing')->count();
        $completed = CompanyRegistration::where('status', 'completed')->count();
        $failed = CompanyRegistration::where('status', 'failed')->count();
        $queried = CompanyRegistration::where('status', 'query')->count();

        return view('admin.cac.index', compact('registrationList', 'pending', 'processing', 'completed', 'failed', 'queried'));
    }

    public function show($id)
    {
        $requests = CompanyRegistration::with(['user', 'transaction'])->findOrFail($id);
        return view('admin.cac.show', compact('requests'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,query',
            'comment' => 'required|string',
            'document.*' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $cacRequest = CompanyRegistration::findOrFail($id);

        $disk = Storage::disk('public');

        // Delete old docs if requested
        if ($request->has('delete_old_docs') && $cacRequest->response_documents) {
            foreach ($cacRequest->response_documents as $oldDoc) {
                if ($disk->exists($oldDoc)) {
                    $disk->delete($oldDoc);
                }
            }
            $cacRequest->response_documents = null;
        }

        if ($request->hasFile('document')) {
            $docs = $cacRequest->response_documents ?? [];
            foreach ($request->file('document') as $file) {
                $filename = 'company_resp_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $docs[] = $file->storeAs('docs', $filename, 'public');
            }
            $cacRequest->response_documents = $docs;
        }

        $cacRequest->status = $request->status;
        $cacRequest->admin_comment = $request->comment;

        if ($request->status === 'failed' && $request->filled('refundAmount')) {
            $refundAmount = $request->refundAmount;
            $wallet = Wallet::where('user_id', $cacRequest->user_id)->first();
            $wallet->increment('balance', $refundAmount);

            $this->transactionService->createTransaction(
                $cacRequest->user_id,
                $refundAmount,
                'Company Registration Refund',
                'Refund for failed company registration',
                'Wallet',
                'Approved'
            );
            $cacRequest->refunded_at = now();
        }

        $cacRequest->save();

        return redirect()->route('admin.company.index')->with('success', 'Status updated successfully.');
    }
}
