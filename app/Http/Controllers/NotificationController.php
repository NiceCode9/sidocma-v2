<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notification count
     */
    public function getUnreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Get notification list
     */
    public function getList(Request $request)
    {
        // $limit = $request->input('limit', 10);

        $notifications = Auth::user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            // ->limit($limit)
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'document',
                    'message' => $data['message'] ?? '',
                    'document_title' => $data['document_title'] ?? '',
                    'document_number' => $data['document_number'] ?? '',
                    'actor_name' => $data['actor_name'] ?? 'Sistem',
                    'folder_name' => $data['folder_name'] ?? '',
                    'action_url' => $data['action_url'] ?? '#',
                    'is_read' => $notification->read_at !== null,
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        if ($notification->data['type'] === 'surat_uploaded') {
            $surat = Surat::find($notification->data['surat_id']);
            $surat->markAsRead();
            event(new \App\Events\SuratReaded($surat, auth()->user()));
        }

        if ($notification->data['type'] === 'document_uploaded') {
            $document = Document::find($notification->data['document_id']);
            $document->documentShare->markAsRead();
        }


        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $count = Auth::user()->unreadNotifications()->count();

        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications marked as read",
            'count' => $count
        ]);
    }

    /**
     * Delete notification
     */
    public function delete($id)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted'
        ]);
    }

    /**
     * Clear all notifications
     */
    public function clearAll()
    {
        $count = Auth::user()->notifications()->count();

        Auth::user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications cleared",
            'count' => $count
        ]);
    }
}
