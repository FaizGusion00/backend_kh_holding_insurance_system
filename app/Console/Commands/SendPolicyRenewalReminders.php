<?php

namespace App\Console\Commands;

use App\Models\MemberPolicy;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPolicyRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:policy-renewals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send policy renewal reminder notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting policy renewal reminder notifications...');

        try {
            $notificationService = new NotificationService();
            
            // Get policies expiring in the next 30 days
            $expiringPolicies = MemberPolicy::with(['user', 'plan'])
                ->where('status', 'active')
                ->where('end_date', '>', now())
                ->where('end_date', '<=', now()->addDays(30))
                ->get();

            $remindersSent = 0;

            foreach ($expiringPolicies as $policy) {
                if ($policy->user && $policy->end_date) {
                    $planName = $policy->plan ? $policy->plan->name : null;
                    $success = $notificationService->createPolicyRenewalReminder(
                        $policy->user_id,
                        $policy->id,
                        $policy->end_date,
                        $planName
                    );

                    if ($success) {
                        $remindersSent++;
                        $endDate = is_string($policy->end_date) ? $policy->end_date : $policy->end_date->format('Y-m-d');
                        $this->line("✓ Reminder sent to {$policy->user->name} for policy expiring on {$endDate}");
                    }
                }
            }

            // Get overdue policies
            $overduePolicies = MemberPolicy::with(['user', 'plan'])
                ->where('status', 'active')
                ->where('end_date', '<', now())
                ->get();

            foreach ($overduePolicies as $policy) {
                if ($policy->user && $policy->end_date) {
                    $planName = $policy->plan ? $policy->plan->name : null;
                    $success = $notificationService->createPolicyRenewalReminder(
                        $policy->user_id,
                        $policy->id,
                        $policy->end_date,
                        $planName
                    );

                    if ($success) {
                        $remindersSent++;
                        $endDate = is_string($policy->end_date) ? $policy->end_date : $policy->end_date->format('Y-m-d');
                        $this->line("⚠ Overdue reminder sent to {$policy->user->name} for policy expired on {$endDate}");
                    }
                }
            }

            $this->info("Policy renewal reminders completed. {$remindersSent} reminders sent.");
            Log::info("Policy renewal reminders sent: {$remindersSent}");

        } catch (\Exception $e) {
            $this->error('Error sending policy renewal reminders: ' . $e->getMessage());
            Log::error('Error sending policy renewal reminders: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
