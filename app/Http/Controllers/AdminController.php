<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PaymentTransaction;
use App\Models\CommissionTransaction;
use App\Models\WithdrawalRequest;
use App\Models\MemberPolicy;
use App\Models\InsurancePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // High-level stats
        $stats = [
            'total_users' => User::count(),
            'total_payments' => PaymentTransaction::where('status', 'paid')->count(),
            'total_commissions' => (CommissionTransaction::where('status', 'posted')->sum('commission_cents') ?? 0) / 100,
            'pending_withdrawals' => WithdrawalRequest::where('status', 'pending')->count(),
            'total_revenue' => (PaymentTransaction::where('status', 'paid')->sum('amount_cents') ?? 0) / 100,
            'active_policies' => MemberPolicy::where('status', 'active')->count(),
            'new_users_30d' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'avg_payment_rm' => round(((int) PaymentTransaction::where('status', 'paid')->avg('amount_cents')) / 100, 2),
            'success_rate' => (function () {
                $total = PaymentTransaction::count();
                if ($total === 0) return 0;
                $paid = PaymentTransaction::where('status', 'paid')->count();
                return round(($paid / max(1, $total)) * 100, 1);
            })(),
        ];

        // Build last 6 months labels
        $months = collect(range(0, 5))
            ->map(function (int $i) {
                $dt = now()->startOfMonth()->subMonths(5 - $i);
                return [
                    'key' => $dt->format('Y-m'),
                    'label' => $dt->format('M'),
                    'start' => $dt->copy(),
                    'end' => $dt->copy()->endOfMonth(),
                ];
            });

        $rangeStart = $months->first()['start'];
        $rangeEnd = $months->last()['end'];

        // Payments aggregate (count and amount)
        $rawPayments = PaymentTransaction::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as cnt, SUM(amount_cents) as sum_cents")
            ->where('status', 'paid')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('ym')
            ->pluck('cnt', 'ym');

        $rawPaymentsAmount = PaymentTransaction::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(amount_cents) as sum_cents")
            ->where('status', 'paid')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('ym')
            ->pluck('sum_cents', 'ym');

        // Commissions aggregate (amount)
        $rawCommissions = CommissionTransaction::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(commission_cents) as sum_cents")
            ->where('status', 'posted')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->groupBy('ym')
            ->pluck('sum_cents', 'ym');

        $chartLabels = $months->pluck('label');
        $paymentsSeries = $months->map(fn ($m) => (int) ($rawPayments[$m['key']] ?? 0));
        $paymentsAmountSeries = $months->map(fn ($m) => round(((int) ($rawPaymentsAmount[$m['key']] ?? 0)) / 100, 2));
        $commissionsSeries = $months->map(fn ($m) => round(((int) ($rawCommissions[$m['key']] ?? 0)) / 100, 2));

        // Build cumulative series to avoid downward spikes when months have no data
        $paymentsAmountCumulative = [];
        $commissionsCumulative = [];
        $runningPay = 0.0;
        $runningCom = 0.0;
        foreach ($paymentsAmountSeries as $v) {
            $runningPay += (float) $v;
            $paymentsAmountCumulative[] = round($runningPay, 2);
        }
        foreach ($commissionsSeries as $v) {
            $runningCom += (float) $v;
            $commissionsCumulative[] = round($runningCom, 2);
        }

        $recentPayments = PaymentTransaction::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentWithdrawals = WithdrawalRequest::with('user')
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::latest()->limit(5)->get();

        // Top performing agents (by commissions)
        $topAgents = CommissionTransaction::selectRaw('earner_user_id, SUM(commission_cents) as total_cents')
            ->where('status', 'posted')
            ->groupBy('earner_user_id')
            ->orderByDesc('total_cents')
            ->limit(5)
            ->with('earner')
            ->get();

        // Revenue by plan
        $revenueByPlan = PaymentTransaction::selectRaw('plan_id, SUM(amount_cents) as sum_cents')
            ->where('status', 'paid')
            ->groupBy('plan_id')
            ->with('plan')
            ->get()
            ->map(function ($row) {
                return [
                    'plan' => optional($row->plan)->name ?? 'Unknown',
                    'amount_rm' => round(((int) $row->sum_cents) / 100, 2),
                ];
            });

        return view('admin.dashboard', compact(
            'stats',
            'recentPayments',
            'recentWithdrawals',
            'recentUsers',
            'chartLabels',
            'paymentsSeries',
            'paymentsAmountSeries',
            'commissionsSeries',
            'paymentsAmountCumulative',
            'commissionsCumulative',
            'topAgents',
            'revenueByPlan'
        ));
    }
}