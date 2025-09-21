<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $perPage = min($request->get('per_page', 20), 50); // Max 50 notifications
            $type = $request->get('type');
            $category = $request->get('category');
            $unreadOnly = $request->get('unread_only', false);

            $query = Notification::where('user_id', $user->id)
                ->with(['relatedUser:id,name,email'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($type) {
                $query->byType($type);
            }

            if ($category) {
                $query->byCategory($category);
            }

            if ($unreadOnly) {
                $query->unread();
            }

            $notifications = $query->paginate($perPage);

            // Get unread count
            $unreadCount = Notification::where('user_id', $user->id)->unread()->count();

            // Transform notifications for frontend
            $transformedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'priority' => $notification->priority,
                    'category' => $notification->category,
                    'is_read' => $notification->isRead(),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'related_user' => $notification->relatedUser ? [
                        'id' => $notification->relatedUser->id,
                        'name' => $notification->relatedUser->name,
                        'email' => $notification->relatedUser->email,
                    ] : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'notifications' => $transformedNotifications,
                    'unread_count' => $unreadCount,
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch notifications'
            ], 500);
        }
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request, int $id)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification marked as read',
                'data' => [
                    'notification_id' => $id,
                    'read_at' => $notification->read_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read for the authenticated user
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $updatedCount = Notification::where('user_id', $user->id)
                ->unread()
                ->update(['read_at' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => 'All notifications marked as read',
                'data' => [
                    'updated_count' => $updatedCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark all notifications as read'
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $unreadCount = Notification::where('user_id', $user->id)->unread()->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'unread_count' => $unreadCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting unread count: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get unread count'
            ], 500);
        }
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $user = auth('api')->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $notification = Notification::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Notification not found'
                ], 404);
            }

            $notification->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete notification'
            ], 500);
        }
    }
}


