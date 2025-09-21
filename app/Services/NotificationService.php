<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create notification for new network member
     */
    public function createNewNetworkMemberNotification($agentId, $newMemberId)
    {
        try {
            $newMember = User::find($newMemberId);
            $agent = User::find($agentId);

            if (!$newMember || !$agent) {
                return false;
            }

            // Notify the agent about new member
            Notification::createNetworkNotification(
                $agentId,
                $newMemberId,
                'New Network Member',
                "New member {$newMember->name} has joined your network",
                [
                    'member_name' => $newMember->name,
                    'member_email' => $newMember->email,
                    'member_nric' => $newMember->nric,
                ]
            );

            // Also notify upline agents (up to 3 levels)
            $this->notifyUplineAgents($agent, $newMember, 3);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating new network member notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create commission earned notification
     */
    public function createCommissionNotification($userId, $amount, $source, $sourceUserId = null, $transactionId = null)
    {
        try {
            $data = [
                'amount' => $amount,
                'source' => $source,
                'transaction_id' => $transactionId,
            ];

            if ($sourceUserId) {
                $sourceUser = User::find($sourceUserId);
                if ($sourceUser) {
                    $data['source_user'] = [
                        'id' => $sourceUser->id,
                        'name' => $sourceUser->name,
                    ];
                }
            }

            Notification::createCommissionNotification($userId, $amount, $source, $data);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating commission notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create payment notification
     */
    public function createPaymentNotification($userId, $amount, $status, $paymentMethod = null, $transactionId = null)
    {
        try {
            $data = [
                'amount' => $amount,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
            ];

            Notification::createPaymentNotification($userId, $amount, $status, $data);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating payment notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create policy renewal reminder
     */
    public function createPolicyRenewalReminder($userId, $policyId, $dueDate, $planName = null)
    {
        try {
            $daysUntilDue = now()->diffInDays($dueDate, false);
            
            if ($daysUntilDue <= 0) {
                $title = 'Policy Renewal Overdue';
                $message = $planName 
                    ? "Your {$planName} policy renewal is overdue. Please renew immediately to avoid coverage interruption."
                    : "Your insurance policy renewal is overdue. Please renew immediately to avoid coverage interruption.";
                $priority = 'urgent';
            } elseif ($daysUntilDue <= 7) {
                $title = 'Policy Renewal Due Soon';
                $message = $planName 
                    ? "Your {$planName} policy expires in {$daysUntilDue} days. Please renew to continue your coverage."
                    : "Your insurance policy expires in {$daysUntilDue} days. Please renew to continue your coverage.";
                $priority = 'high';
            } else {
                $title = 'Policy Renewal Reminder';
                $message = $planName 
                    ? "Your {$planName} policy expires in {$daysUntilDue} days. Consider renewing early."
                    : "Your insurance policy expires in {$daysUntilDue} days. Consider renewing early.";
                $priority = 'normal';
            }

            Notification::create([
                'user_id' => $userId,
                'type' => 'policy_renewal_reminder',
                'title' => $title,
                'message' => $message,
                'data' => [
                    'policy_id' => $policyId,
                    'due_date' => $dueDate,
                    'days_until_due' => $daysUntilDue,
                    'plan_name' => $planName,
                ],
                'priority' => $priority,
                'category' => 'reminder',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating policy renewal reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create payment due reminder
     */
    public function createPaymentDueReminder($userId, $amount, $dueDate, $description = null)
    {
        try {
            $daysUntilDue = now()->diffInDays($dueDate, false);
            
            if ($daysUntilDue <= 0) {
                $title = 'Payment Overdue';
                $message = "Your payment of RM{$amount} is overdue. Please make payment immediately.";
                $priority = 'urgent';
            } elseif ($daysUntilDue <= 3) {
                $title = 'Payment Due Soon';
                $message = "Your payment of RM{$amount} is due in {$daysUntilDue} days.";
                $priority = 'high';
            } else {
                $title = 'Payment Reminder';
                $message = "Your payment of RM{$amount} is due in {$daysUntilDue} days.";
                $priority = 'normal';
            }

            if ($description) {
                $message .= " ({$description})";
            }

            Notification::create([
                'user_id' => $userId,
                'type' => 'payment_due_reminder',
                'title' => $title,
                'message' => $message,
                'data' => [
                    'amount' => $amount,
                    'due_date' => $dueDate,
                    'days_until_due' => $daysUntilDue,
                    'description' => $description,
                ],
                'priority' => $priority,
                'category' => 'reminder',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating payment due reminder: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create MLM level upgrade notification
     */
    public function createLevelUpgradeNotification($userId, $newLevel, $oldLevel = null)
    {
        try {
            $title = 'Network Level Upgrade!';
            $message = $oldLevel 
                ? "Congratulations! You've been upgraded from Level {$oldLevel} to Level {$newLevel}!"
                : "Congratulations! You've reached Network Level {$newLevel}!";

            Notification::create([
                'user_id' => $userId,
                'type' => 'level_upgrade',
                'title' => $title,
                'message' => $message,
                'data' => [
                    'new_level' => $newLevel,
                    'old_level' => $oldLevel,
                ],
                'priority' => 'high',
                'category' => 'network',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating level upgrade notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create system notification
     */
    public function createSystemNotification($userId, $title, $message, $priority = 'normal', $data = null)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'priority' => $priority,
                'category' => 'system',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating system notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create welcome notification for new users
     */
    public function createWelcomeNotification($userId)
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            Notification::create([
                'user_id' => $userId,
                'type' => 'welcome',
                'title' => 'Welcome to KH Holdings Insurance!',
                'message' => "Welcome {$user->name}! Thank you for joining KH Holdings Insurance. Explore your dashboard to get started with your insurance journey.",
                'data' => [
                    'user_name' => $user->name,
                ],
                'priority' => 'normal',
                'category' => 'system',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error creating welcome notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify upline agents about new network member
     */
    private function notifyUplineAgents($agent, $newMember, $levels = 3)
    {
        try {
            $currentAgent = $agent;
            $level = 1;

            while ($currentAgent && $currentAgent->referrer_code && $level <= $levels) {
                $uplineAgent = User::where('agent_code', $currentAgent->referrer_code)->first();
                
                if ($uplineAgent) {
                    Notification::create([
                        'user_id' => $uplineAgent->id,
                        'type' => 'network_growth',
                        'title' => 'Network Growth',
                        'message' => "New member {$newMember->name} joined your network through {$agent->name} (Level {$level})",
                        'data' => [
                            'new_member' => [
                                'id' => $newMember->id,
                                'name' => $newMember->name,
                                'email' => $newMember->email,
                            ],
                            'direct_referrer' => [
                                'id' => $agent->id,
                                'name' => $agent->name,
                            ],
                            'level' => $level,
                        ],
                        'priority' => 'normal',
                        'category' => 'network',
                        'related_user_id' => $newMember->id,
                    ]);

                    $currentAgent = $uplineAgent;
                    $level++;
                } else {
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error notifying upline agents: ' . $e->getMessage());
        }
    }

    /**
     * Clean up old notifications (older than 90 days)
     */
    public function cleanupOldNotifications($days = 90)
    {
        try {
            $deletedCount = Notification::where('created_at', '<', now()->subDays($days))->delete();
            Log::info("Cleaned up {$deletedCount} old notifications");
            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Error cleaning up old notifications: ' . $e->getMessage());
            return 0;
        }
    }
}
