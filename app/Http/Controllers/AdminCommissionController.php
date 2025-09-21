<?php

namespace App\Http\Controllers;

use App\Models\CommissionTransaction;
use App\Models\CommissionRate;
use Illuminate\Http\Request;

class AdminCommissionController extends Controller
{
    public function index()
    {
        $commissions = CommissionTransaction::with(['earner', 'source', 'plan'])
            ->where('status', 'posted')
            ->latest()
            ->paginate(15);

        $totalCommissions = CommissionTransaction::where('status', 'posted')->sum('commission_cents') / 100;
        $monthlyCommissions = CommissionTransaction::where('status', 'posted')
            ->whereMonth('created_at', now()->month)
            ->sum('commission_cents') / 100;

        return view('admin.commissions.index', compact('commissions', 'totalCommissions', 'monthlyCommissions'));
    }

    public function transactions()
    {
        $transactions = CommissionTransaction::with(['earner', 'source', 'plan'])
            ->latest()
            ->paginate(15);

        return view('admin.commissions.transactions', compact('transactions'));
    }
}
