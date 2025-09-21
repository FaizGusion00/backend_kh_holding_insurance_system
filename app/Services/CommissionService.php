<?php

namespace App\Services;

use App\Models\AgentWallet;
use App\Models\CommissionRate;
use App\Models\CommissionTransaction;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommissionService
{
    public function disburseForPayment(PaymentTransaction $payment): void
    {
        $sourceUser = $payment->user;
        $amountCents = $payment->amount_cents;
        $planId = $payment->plan_id;

        // Build upline chain up to 5 levels using referrer_code -> agent_code
        $upline = [];
        $current = $sourceUser;
        for ($level = 1; $level <= 5; $level++) {
            $refCode = $current->referrer_code;
            if (! $refCode) break;
            $sponsor = User::where('agent_code', $refCode)->first();
            if (! $sponsor) break;
            $upline[] = $sponsor;
            $current = $sponsor;
        }

        DB::transaction(function () use ($upline, $planId, $amountCents, $sourceUser, $payment) {
            foreach ($upline as $idx => $earner) {
                $level = $idx + 1;
                $rate = CommissionRate::where('plan_id', $planId)->where('level', $level)->first();
                if (! $rate) continue;

                $commission = 0;
                if (! is_null($rate->fixed_amount_cents)) {
                    $commission = (int) $rate->fixed_amount_cents;
                } elseif (! is_null($rate->rate_percent)) {
                    $commission = (int) round($amountCents * ((float) $rate->rate_percent) / 100);
                }
                if ($commission <= 0) continue;

                $ct = CommissionTransaction::create([
                    'earner_user_id' => $earner->id,
                    'source_user_id' => $sourceUser->id,
                    'plan_id' => $planId,
                    'payment_transaction_id' => $payment->id,
                    'level' => $level,
                    'basis_amount_cents' => $amountCents,
                    'commission_cents' => $commission,
                    'status' => 'posted',
                    'posted_at' => now(),
                ]);

                $wallet = AgentWallet::firstOrCreate(['user_id' => $earner->id]);
                $wallet->balance_cents += $commission;
                $wallet->save();

                $wallet->transactions()->create([
                    'type' => 'credit',
                    'source' => 'commission',
                    'amount_cents' => $commission,
                    'commission_transaction_id' => $ct->id,
                ]);

                // Create commission notification
                try {
                    $notificationService = new NotificationService();
                    $notificationService->createCommissionNotification(
                        $earner->id,
                        $commission / 100, // Convert cents to dollars
                        "Level {$level} commission from {$sourceUser->name}",
                        $sourceUser->id,
                        $ct->id
                    );
                } catch (\Exception $e) {
                    Log::error("Failed to create commission notification for user {$earner->id}: " . $e->getMessage());
                }
            }
        });
    }
}


