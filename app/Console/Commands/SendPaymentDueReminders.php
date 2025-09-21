<?php

namespace App\Console\Commands;

use App\Models\MemberPolicy;
use App\Models\PaymentTransaction;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPaymentDueReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:payment-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment due reminder notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting payment due reminder notifications...');

        try {
            $notificationService = new NotificationService();
            $remindersSent = 0;

            // Get pending payments that are due soon or overdue
            $pendingPayments = PaymentTransaction::with(['user', 'plan'])
                ->where('status', 'pending')
                ->where('created_at', '<=', now()->subDays(1)) // At least 1 day old
                ->get();

            foreach ($pendingPayments as $payment) {
                if ($payment->user) {
                    $daysSinceCreated = now()->diffInDays($payment->created_at);
                    $amount = $payment->amount_cents / 100;
                    
                    // Send reminder based on how old the payment is
                    if ($daysSinceCreated >= 7) {
                        $dueDate = $payment->created_at->addDays(7); // Assume 7 days payment term
                        $success = $notificationService->createPaymentDueReminder(
                            $payment->user_id,
                            $amount,
                            $dueDate,
                            'Insurance Premium Payment'
                        );

                        if ($success) {
                            $remindersSent++;
                            $this->line("⚠ Payment reminder sent to {$payment->user->name} for RM{$amount}");
                        }
                    }
                }
            }

            // Get active policies with upcoming monthly payments
            $activePolicies = MemberPolicy::with(['user', 'plan'])
                ->where('status', 'active')
                ->whereNotNull('start_date')
                ->get();

            foreach ($activePolicies as $policy) {
                if ($policy->user && $policy->start_date) {
                    // Calculate next payment date (assuming monthly payments)
                    $nextPaymentDate = $policy->start_date->copy();
                    while ($nextPaymentDate->isPast()) {
                        $nextPaymentDate->addMonth();
                    }

                    // Send reminder if payment is due within 3 days
                    if ($nextPaymentDate->diffInDays(now(), false) <= 3 && $nextPaymentDate->diffInDays(now(), false) >= 0) {
                        $planPrice = 40; // Default price, you can get from plan if available
                        
                        if ($policy->plan && $policy->plan->name) {
                            $planName = $policy->plan->name;
                        } else {
                            $planName = 'Insurance Plan';
                        }
                        $success = $notificationService->createPaymentDueReminder(
                            $policy->user_id,
                            $planPrice,
                            $nextPaymentDate,
                            'Monthly premium for ' . $planName
                        );

                        if ($success) {
                            $remindersSent++;
                            $this->line('Monthly payment reminder sent to ' . $policy->user->name . ' for ' . $nextPaymentDate->format('Y-m-d'));
                        }
                    }
                }
            }

            $this->info("Payment due reminders completed. {$remindersSent} reminders sent.");
            Log::info("Payment due reminders sent: {$remindersSent}");

        } catch (\Exception $e) {
            $this->error('Error sending payment due reminders: ' . $e->getMessage());
            Log::error('Error sending payment due reminders: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
