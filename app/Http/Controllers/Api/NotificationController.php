<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $userType = $this->getUserType($request);

        $notifications = Notification::forUser($userId, $userType)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(Request $request)
    {
        $userId = $request->user()->id;
        $userType = $this->getUserType($request);

        $count = Notification::forUser($userId, $userType)->unread()->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Send a notification to a user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'user_type' => 'required|in:patient,hospital,admin',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $senderId = $request->user()->id;
        $senderType = $this->getUserType($request);

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'user_type' => $request->user_type,
            'title' => $request->title,
            'message' => $request->message,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully',
            'data' => $notification
        ], 201);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $userId = $request->user()->id;
        $userType = $this->getUserType($request);

        $notification = Notification::forUser($userId, $userType)->findOrFail($id);
        $notification->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $userId = $request->user()->id;
        $userType = $this->getUserType($request);

        Notification::forUser($userId, $userType)->unread()->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Request $request, $id)
    {
        $userId = $request->user()->id;
        $userType = $this->getUserType($request);

        $notification = Notification::forUser($userId, $userType)->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Determine user type from request.
     */
    private function getUserType(Request $request): string
    {
        $guard = $request->route()->middleware();

        if (in_array('auth:patient', $guard) || $request->user() instanceof \App\Models\Patient) {
            return 'patient';
        }
        if (in_array('auth:hospital', $guard) || $request->user() instanceof \App\Models\Hospital) {
            return 'hospital';
        }
        if (in_array('auth:admin', $guard) || $request->user() instanceof \App\Models\Admin) {
            return 'admin';
        }

        // Fallback: check the user model class
        $userClass = get_class($request->user());
        return match (true) {
            str_contains($userClass, 'Patient') => 'patient',
            str_contains($userClass, 'Hospital') => 'hospital',
            str_contains($userClass, 'Admin') => 'admin',
            default => 'patient',
        };
    }
}
