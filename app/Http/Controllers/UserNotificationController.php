<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Http\Resources\UserNotificationResource;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Log;

class UserNotificationController extends Controller
{
    private $jwtSecret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

    private function getUserIdFromToken(Request $request)
    {
        // Obtener el token desde el encabezado de autorización
        $token = $request->bearerToken();

        if (!$token) {
            throw new \Exception('Token not provided', 401);
        }

        try {
            // Decodificar el token para obtener el ID del usuario
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return $decoded->sub; // Asumiendo que el ID del usuario está en el campo 'sub'
        } catch (\Exception $e) {
            throw new \Exception('Invalid token', 401);
        }
    }

    public function getUserNotifications(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $userNotifications = UserNotification::where('user_id', $userId)
                                                 ->with('notification')
                                                 ->orderBy('created_at', 'desc')
                                                 ->get();

            return UserNotificationResource::collection($userNotifications);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving user notifications', 'error' => $e->getMessage()], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string',
        ]);

        try {
            $userId = $this->getUserIdFromToken($request);
            $userNotification = UserNotification::where('user_id', $userId)
                                                 ->where('notification_id', $request->input('notification_id'))
                                                 ->firstOrFail();
            $userNotification->update(['is_read' => true]);
            return response()->json(['message' => 'Notification marked as read']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error marking notification as read', 'error' => $e->getMessage()], 500);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);

            // Marcar todas las notificaciones del usuario como leídas
            UserNotification::where('user_id', $userId)
                            ->update(['is_read' => true]);

            return response()->json(['message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error marking all notifications as read', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|string',
        ]);

        try {
            $userId = $this->getUserIdFromToken($request);
            $userNotification = UserNotification::where('user_id', $userId)
                                                 ->where('notification_id', $request->input('notification_id'))
                                                 ->firstOrFail();
            $userNotification->delete();

            return response()->json(['message' => 'Notification deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting notification', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroyAll(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);

            // Eliminar todas las notificaciones del usuario
            UserNotification::where('user_id', $userId)->delete();

            return response()->json(['message' => 'All notifications deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting all notifications', 'error' => $e->getMessage()], 500);
        }
    }

    public function sendTestNotification(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $user = User::findOrFail($userId);

            $notificationData = [
                'icon' => 'test-icon',
                'image' => 'test-image-url',
                'title' => 'Test Notification',
                'description' => 'This is a test notification.',
                'time' => now()->toIso8601String(),
                'link' => '/test-link',
                'useRouter' => true,
            ];

            // Enviar la notificación al usuario
            $user->notify(new ActivityNotification($notificationData));

            // Crear una entrada en la tabla user_notifications
            UserNotification::create([
                'user_id' => $userId,
                'notification_id' => $user->notifications()->latest()->first()->id,
                'is_read' => false,
                'is_deleted' => false,
            ]);

            return response()->json(['message' => 'Test notification sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error sending test notification', 'error' => $e->getMessage()], 500);
        }
    }

    public function sendAdminTestNotification(Request $request)
    {
        try {
            $notificationData = [
                'icon' => 'test-icon',
                'image' => 'test-image-url',
                'title' => 'Test Notification',
                'description' => 'This is a test notification.',
                'time' => now()->toIso8601String(),
                'link' => '/test-link',
                'useRouter' => true,
            ];

            $notifications = [];

            // Enviar la notificación a todos los administradores
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new ActivityNotification($notificationData));

                // Crear una entrada en la tabla user_notifications
                $notification = UserNotification::create([
                    'user_id' => $admin->id,
                    'notification_id' => $admin->notifications()->latest()->first()->id,
                    'is_read' => false,
                    'is_deleted' => false,
                ]);

                $notifications[] = new UserNotificationResource($notification);
            }

            return response()->json(['message' => 'Test notification sent to all admins successfully', 'data' => $notifications]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error sending test notification', 'error' => $e->getMessage()], 500);
        }
    }
}