<?php

namespace App\Http\Controllers;

use App\Models\BusinessRegistration;
use App\Models\CompanyRegistration;
use App\Models\BvnPhoneSearch;
use App\Models\Enrollment;
use App\Models\IpeRequest;
use App\Models\ModificationRequest;
use App\Models\ModIpeClearance;
use App\Models\NinModification;
use App\Models\NinValidation;
use App\Models\PersonalizeRequest;
use App\Models\Popup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {

        $status = auth()->user()->kyc_status;

        $kycPending = session('kyc_pending', false);

        if ($status == 'Pending') {
            $kycPending = true;
        }
        if (auth()->user()->role == 'admin') {
            $totalRevenue = Transaction::where('status', 'Approved')->sum(DB::raw('CAST(amount AS DECIMAL(15,2))'));

            $totalUsers = User::count();

            $approvedToday = DB::table('transactions')
                ->where('status', 'Approved')
                ->whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])
                ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                ->selectRaw('SUM(CAST(amount AS DECIMAL(15,2))) as total')
                ->value('total');

            $ninModifications = NinModification::whereIn('status', ['pending'])->count();
            $bvnModifications = ModificationRequest::whereIn('status', ['pending', 'processing'])->count();
            $bvnSearch = BvnPhoneSearch::whereIn('status', ['pending'])->count();
            $modipe = ModIpeClearance::whereIn('status', ['pending'])->count();
            $personalizePending = PersonalizeRequest::whereIn('status', ['Pending', 'In-Progress'])->count();

            $cacPending = BusinessRegistration::whereIn('status', ['pending', 'processing'])->count();
            $companyPending = CompanyRegistration::whereIn('status', ['pending', 'processing'])->count();

            $ninValidations = NinValidation::whereIn('status', ['Pending', 'In-Progress'])
                ->whereNull('tag')->count();

            $ninDelink = NinValidation::whereIn('status', ['Pending', 'In-Progress'])
                ->where('tag', 'DELINK')->count();

            $enrollmentsCount = Enrollment::whereIn('status', ['submitted', 'procesing'])
                ->count();

            $totalWalletBalance = DB::table('wallets')->selectRaw('SUM(balance) as total')->value('total');
            $totalBonusBalance = DB::table('bonus_histories')->selectRaw('SUM(amount) as total')->value('total');
            $ipePending = IpeRequest::where('status', 'pending')->count();
            $metrics = [
                [
                    'title' => 'Total Revenue',
                    'value' => '₦'.number_format($totalRevenue, 2),
                    'icon' => 'bi-cash-stack',
                    'bg' => 'success',
                    'href' => '#',
                ],
                [
                    'title' => 'Total Wallet Balance',
                    'value' => '₦'.number_format($totalWalletBalance, 2),
                    'icon' => 'bi-wallet2',
                    'bg' => 'warning',
                    'href' => url('user/wallet'),
                ],
                [
                    'title' => 'Total Bonus Balance',
                    'value' => '₦'.number_format($totalBonusBalance, 2),
                    'icon' => 'bi-wallet2',
                    'bg' => 'info',
                    'href' => url('user/wallet'),
                ],
                [
                    'title' => 'Funding Today',
                    'value' => '₦'.number_format($approvedToday, 2),
                    'icon' => 'bi-wallet2',
                    'bg' => 'primary',
                    'href' => 'https://business.palmpay.com/#/login',
                ],
                [
                    'title' => 'Total Users',
                    'value' => number_format($totalUsers),
                    'icon' => 'bi-people-fill',
                    'bg' => 'danger',
                    'href' => url('admin/users'),
                ],
                [
                    'title' => 'BVN Search (Phone)',
                    'value' => number_format($bvnSearch),
                    'icon' => 'bi-search',
                    'bg' => 'primary',
                    'href' => url('admin/bvn-services'),
                ],
                [
                    'title' => 'NIN Modifications',
                    'value' => number_format($ninModifications),
                    'icon' => 'bi-fingerprint',
                    'bg' => 'dark',
                    'href' => url('admin/mod-services'),
                ],
                [
                    'title' => 'BVN Modifications',
                    'value' => number_format($bvnModifications),
                    'icon' => 'bi-fingerprint',
                    'bg' => 'warning',
                    'href' => url('admin/bvn-modification'),
                ],
                [
                    'title' => 'IPE Clearance',
                    'value' => number_format($ipePending),
                    'icon' => 'bi-check2-circle',
                    'bg' => 'warning',
                    'href' => url('admin/ipe-index'),
                ],
                [
                    'title' => 'MOD IPE Clearance',
                    'value' => number_format($modipe),
                    'icon' => 'bi-check2-circle',
                    'bg' => 'info',
                    'href' => url('admin/mod-ipe-index'),
                ],
                [
                    'title' => 'Personalization',
                    'value' => number_format($personalizePending),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'dark',
                    'href' => 'https://s8v.ng/nin-verification',
                ],
                [
                    'title' => 'NIN Validation',
                    'value' => number_format($ninValidations),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'success',
                    'href' => url('admin/nin-services'),
                ],
                [
                    'title' => 'NIN DELINK',
                    'value' => number_format($ninDelink),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'primary',
                    'href' => url('admin/delink-services'),
                ],
                [
                    'title' => 'BVN User',
                    'value' => number_format($enrollmentsCount),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'dark',
                    'href' => url('admin/enrollment-list'),
                ],
                [
                    'title' => 'CAC (Biz Name)',
                    'value' => number_format($cacPending),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'success',
                    'href' => url('admin/cac-biz-list'),
                ],
                [
                    'title' => 'CAC (Company)',
                    'value' => number_format($companyPending),
                    'icon' => 'bi bi-hourglass-split',
                    'bg' => 'primary',
                    'href' => url('admin/company-registrations'),
                ]
            ];

            $depositChartData = [
                'Approved' => (float) Transaction::whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])->where('status', 'Approved')->sum('amount'),
                'Pending' => (float) Transaction::whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])->where('status', 'Pending')->sum('amount'),
                'Rejected' => (float) Transaction::whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])->where('status', 'Rejected')->sum('amount'),
            ];

            $depositChartData = [
                'Funding' => DB::table('transactions')
                    ->where('status', 'Approved')
                    ->whereIn('service_type', ['Wallet Topup', 'Admin Top Up'])
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->selectRaw('SUM(CAST(amount AS DECIMAL(15,2))) as total')
                    ->value('total'),

                'Expenses' => DB::table('transactions')
                    ->where('status', 'Approved')
                    ->whereNotIn('service_type', ['Wallet Topup', 'Admin Top Up'])
                    ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->selectRaw('SUM(CAST(amount AS DECIMAL(15,2))) as total')
                    ->value('total'),

            ];

            $topFunders = DB::table('transactions as t')
                ->join('users as u', 't.user_id', '=', 'u.id')
                ->where('t.status', 'Approved')
                ->whereIn('t.service_type', ['Wallet Topup', 'Admin Top Up'])
                ->whereBetween('t.created_at', [now()->startOfDay(), now()->endOfDay()])
                ->select(
                    'u.name',
                    'u.email',
                    DB::raw('SUM(CAST(t.amount AS DECIMAL(15,2))) as total_funding')
                )
                ->groupBy('u.id', 'u.name', 'u.email')
                ->orderByDesc('total_funding')
                ->limit(5)
                ->get();
        }
        $popup = Popup::where('is_active', true)->first();

        return view('user.dashboard', [
            'kycPending' => $kycPending,
            'status' => $status,
            'metrics' => $metrics ?? null,
            'depositChartData' => $depositChartData ?? null,
            'topFunders' => $topFunders ?? collect(),
            'popup' => $popup,
        ]);
    }
}
