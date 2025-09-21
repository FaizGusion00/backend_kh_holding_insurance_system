<?php

namespace App\Http\Controllers;

use App\Models\AgentWallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function summary()
    {
        $wallet = AgentWallet::firstOrCreate(['user_id' => auth('api')->id()]);
        return response()->json(['status' => 'success', 'data' => [
            'balance' => $wallet->balance_cents / 100,
            'total_earned' => (\App\Models\CommissionTransaction::where('earner_user_id', auth('api')->id())->where('status', 'posted')->sum('commission_cents') ?? 0) / 100,
            'recent_transactions' => $wallet->transactions()->latest()->limit(10)->get(),
            'withdrawal_requests' => WithdrawalRequest::where('user_id', auth('api')->id())->latest()->get(),
        ]]);
    }

    public function balance()
    {
        $wallet = AgentWallet::firstOrCreate(['user_id' => auth('api')->id()]);
        return response()->json(['status' => 'success', 'data' => [
            'balance' => $wallet->balance_cents / 100,
            'total_earned' => (\App\Models\CommissionTransaction::where('earner_user_id', auth('api')->id())->where('status', 'posted')->sum('commission_cents') ?? 0) / 100,
        ]]);
    }

    public function transactions(Request $request)
    {
        $wallet = AgentWallet::firstOrCreate(['user_id' => auth('api')->id()]);
        $tx = $wallet->transactions()->latest()->paginate(15);
        return response()->json(['status' => 'success', 'data' => $tx]);
    }

    public function requestWithdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string',
            'bank_account_number' => 'required|string',
            'bank_account_owner' => 'required|string',
        ]);
        $amountCents = (int) round($validated['amount'] * 100);
        $wallet = AgentWallet::firstOrCreate(['user_id' => auth('api')->id()]);
        if ($wallet->balance_cents < $amountCents) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient balance'], 422);
        }
        $req = WithdrawalRequest::create([
            'user_id' => auth('api')->id(),
            'amount_cents' => $amountCents,
            'status' => 'pending',
            'bank_meta' => [
                'bank_name' => $validated['bank_name'],
                'bank_account_number' => $validated['bank_account_number'],
                'bank_account_owner' => $validated['bank_account_owner'],
            ],
        ]);
        // Actual deduction will happen when admin marks as paid; keep balance now for safety
        return response()->json(['status' => 'success', 'data' => $req]);
    }

    public function withdrawals()
    {
        $items = WithdrawalRequest::where('user_id', auth('api')->id())->latest()->paginate(15);
        return response()->json(['status' => 'success', 'data' => $items]);
    }
}


