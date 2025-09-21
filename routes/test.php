<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;

// Test route to create sample notifications
Route::get('/test/notifications', function () {
    $user = User::first();
    
    if (!$user) {
        return response()->json(['error' => 'No users found']);
    }

    $notificationService = new NotificationService();
    
    // Create test notifications
    $notificationService->createWelcomeNotification($user->id);
    $notificationService->createCommissionNotification($user->id, 25.00, 'Level 1 referral');
    $notificationService->createPaymentNotification($user->id, 40.00, 'completed');
    
    $unreadCount = Notification::where('user_id', $user->id)->unread()->count();
    $totalCount = Notification::where('user_id', $user->id)->count();
    
    return response()->json([
        'message' => 'Test notifications created',
        'user_id' => $user->id,
        'total_notifications' => $totalCount,
        'unread_count' => $unreadCount
    ]);
});

// Test route to get notifications without auth
Route::get('/test/notifications/list', function () {
    $user = User::first();
    
    if (!$user) {
        return response()->json(['error' => 'No users found']);
    }
    
    $notifications = Notification::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    return response()->json([
        'notifications' => $notifications,
        'count' => $notifications->count()
    ]);
});
