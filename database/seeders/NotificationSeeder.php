<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user for testing
        $user = User::first();
        
        if (!$user) {
            $this->command->info('No users found. Please create a user first.');
            return;
        }

        // Create sample notifications
        $notifications = [
            [
                'type' => 'welcome',
                'title' => 'Welcome to KH Holdings Insurance!',
                'message' => "Welcome {$user->name}! Thank you for joining KH Holdings Insurance. Explore your dashboard to get started with your insurance journey.",
                'priority' => 'normal',
                'category' => 'system',
                'data' => ['user_name' => $user->name],
            ],
            [
                'type' => 'commission_earned',
                'title' => 'Commission Earned',
                'message' => 'You earned RM25.00 commission from Level 1 referral',
                'priority' => 'normal',
                'category' => 'commission',
                'data' => ['amount' => 25.00, 'source' => 'Level 1 referral'],
            ],
            [
                'type' => 'payment_update',
                'title' => 'Payment Received',
                'message' => 'Payment of RM40.00 has been successfully processed',
                'priority' => 'normal',
                'category' => 'payment',
                'data' => ['amount' => 40.00, 'status' => 'completed'],
            ],
            [
                'type' => 'new_network_member',
                'title' => 'New Network Member',
                'message' => 'New member John Doe has joined your network',
                'priority' => 'normal',
                'category' => 'network',
                'data' => ['member_name' => 'John Doe', 'member_email' => 'john@example.com'],
            ],
            [
                'type' => 'policy_renewal_reminder',
                'title' => 'Policy Renewal Due Soon',
                'message' => 'Your MediPlan Coop policy expires in 7 days. Please renew to continue your coverage.',
                'priority' => 'high',
                'category' => 'reminder',
                'data' => ['policy_id' => 1, 'due_date' => now()->addDays(7), 'days_until_due' => 7],
            ],
            [
                'type' => 'payment_due_reminder',
                'title' => 'Payment Due Soon',
                'message' => 'Your payment of RM40.00 is due in 3 days.',
                'priority' => 'high',
                'category' => 'reminder',
                'data' => ['amount' => 40.00, 'due_date' => now()->addDays(3), 'days_until_due' => 3],
            ],
            [
                'type' => 'level_upgrade',
                'title' => 'Network Level Upgrade!',
                'message' => 'Congratulations! You\'ve been upgraded from Level 1 to Level 2!',
                'priority' => 'high',
                'category' => 'network',
                'data' => ['new_level' => 2, 'old_level' => 1],
            ],
            [
                'type' => 'system',
                'title' => 'System Maintenance Notice',
                'message' => 'Scheduled maintenance will occur on Sunday from 2:00 AM to 4:00 AM. Services may be temporarily unavailable.',
                'priority' => 'normal',
                'category' => 'system',
                'data' => ['maintenance_date' => now()->addDays(5)],
            ],
        ];

        foreach ($notifications as $notificationData) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'priority' => $notificationData['priority'],
                'category' => $notificationData['category'],
                'data' => $notificationData['data'],
                'read_at' => rand(0, 1) ? null : now()->subHours(rand(1, 24)), // Some read, some unread
            ]);
        }

        $this->command->info('Sample notifications created successfully!');
    }
}
