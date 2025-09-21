<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\AgentWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminWithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = WithdrawalRequest::with('user')->latest()->paginate(15);
        
        $stats = [
            'pending' => WithdrawalRequest::where('status', 'pending')->count(),
            'approved' => WithdrawalRequest::where('status', 'approved')->count(),
            'paid' => WithdrawalRequest::where('status', 'paid')->count(),
            'rejected' => WithdrawalRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function show(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load('user');
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(WithdrawalRequest $withdrawal)
    {
        $withdrawal->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'Withdrawal request approved');
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        $request->validate(['reason' => 'nullable|string']);
        
        $withdrawal->update([
            'status' => 'rejected',
            'meta' => array_merge($withdrawal->meta ?? [], ['rejection_reason' => $request->reason])
        ]);

        return redirect()->back()->with('success', 'Withdrawal request rejected');
    }

    public function markPaid(WithdrawalRequest $withdrawal)
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Deduct from user's wallet
            $wallet = AgentWallet::where('user_id', $withdrawal->user_id)->first();
            if ($wallet) {
                $wallet->balance_cents -= $withdrawal->amount_cents;
                $wallet->save();

                // Create wallet transaction record
                $wallet->transactions()->create([
                    'type' => 'debit',
                    'source' => 'withdrawal',
                    'amount_cents' => $withdrawal->amount_cents,
                    'withdrawal_request_id' => $withdrawal->id,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Withdrawal marked as paid');
    }
}
