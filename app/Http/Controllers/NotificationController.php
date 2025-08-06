<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Notifications\ActivityNotification;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
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

    public function index(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $user = User::findOrFail($userId);

            // Obtener las notificaciones del usuario
            $notifications = $user->notifications;

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving notifications', 'error' => $e->getMessage()], 500);
        }
    }

    public function getNotifications(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $user = User::findOrFail($userId);

            // Obtener las notificaciones del usuario
            $notifications = $user->notifications;

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving notifications', 'error' => $e->getMessage()], 500);
        }
    }

    public function getUserNotifications(Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $userNotifications = UserNotification::where('user_id', $userId)->get();

            return response()->json($userNotifications);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving user notifications', 'error' => $e->getMessage()], 500);
        }
    }

    public function getAdminNotifications(Request $request)
    {
        try {
            $admins = User::where('role', 'admin')->get();
            $notifications = [];

            foreach ($admins as $admin) {
                $adminNotifications = $admin->notifications;
                foreach ($adminNotifications as $notification) {
                    $notifications[] = $notification;
                }
            }

            return response()->json($notifications);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving admin notifications', 'error' => $e->getMessage()], 500);
        }
    }
    public function markAsRead(Request $request, $id)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $userNotification = UserNotification::where('user_id', $userId)
                                                 ->where('notification_id', $id)
                                                 ->firstOrFail();
            $userNotification->update(['read_at' => now()]);
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
                            ->update(['read_at' => now()]);

            return response()->json(['message' => 'All notifications marked as read']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error marking all notifications as read', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $userId = $this->getUserIdFromToken($request);
            $userNotification = UserNotification::where('user_id', $userId)
                                                 ->where('id', $id)
                                                 ->firstOrFail();
            $userNotification->delete();

            return response()->json(['message' => 'Notification deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting notification', 'error' => $e->getMessage()], 500);
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

            // Enviar la notificación a todos los administradores
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                try {
                    $admin->notify(new ActivityNotification($notificationData));
                } catch (\Exception $e) {
                    Log::error('Error sending notification to admin ID ' . $admin->id . ': ' . $e->getMessage());
                }
            }

            return response()->json(['message' => 'Test notification sent to all admins successfully', 'admins' => $admins]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error sending test notification', 'error' => $e->getMessage()], 500);
        }
    }
}