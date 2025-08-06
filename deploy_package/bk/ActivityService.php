<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Station;
use App\Models\Client;
use App\Notifications\ActivityNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\UserNotification;

class ActivityService
{
    private $jwtSecret = 's3cR3tK3yF0rJWTt0k3nG3n3r4t10n';

    public function logActivity($model, $userId, $ticketId = null, Request $request)
    {
        $original = $model->getOriginal();
        $changes = $model->getChanges();
        $changedFields = array_keys($changes);

        // Filtrar los campos `updated_at` y `created_at`
        $changedFields = array_filter($changedFields, function($field) {
            return !in_array($field, ['updated_at', 'created_at']);
        });

        // Crear la descripción detallada
        $description = class_basename($model) . ' updated. Changes:';
        $detailedChanges = [];
        foreach ($changedFields as $field) {
            if ($field !== 'status' && isset($model->$field)) {
                $detailedChanges[] = ucfirst($field) . ': ' . $model->$field;
            }
        }
        if (!empty($detailedChanges)) {
            $description .= ' ' . implode('. ', $detailedChanges) . '.';
        }

        // Verificar si hay un cambio importante en el campo `status`
        $importantChange = null;
        $newStatus = null;
        if (isset($changes['status'])) {
            $originalStatusText = $original['status'] == 1 ? 'Active' : 'Suspended';
            $newStatusText = $changes['status'] == 1 ? 'Active' : 'Suspended';
            $importantChange = 'Status changed to ' . $newStatusText;
            $newStatus = $newStatusText; // Obtener el nuevo valor de status como texto
        }

        // Determinar station_id y client_id
        $stationId = $model instanceof Station ? $model->id : null;
        $clientId = $model instanceof Client ? $model->id : ($model->client_id ?? null);

        // Registrar la actividad
        Activity::create([
            'user_id' => $userId,
            'model_type' => class_basename($model),
            'model_id' => $model->id,
            'station_id' => $stationId,
            'client_id' => $clientId,
            'action' => 'updated',
            'description' => $description,
            'important_change' => $importantChange,
            'status' => $newStatus,
            'ticket_id' => $ticketId,
        ]);

        // Obtener el ID del usuario desde el token
        $userId = $this->getUserIdFromToken($request);
        $user = User::findOrFail($userId);

        // Determinar la imagen según el tipo de modelo
        if (class_basename($model) == 'Station') {
            $image = $model->image;
            $image = 'https://domintapi.com/images/station/' . $image;
        } elseif (class_basename($model) == 'Client') {
            $image = $model->image;
            $image = 'https://domintapi.com/images/client/' . $image;
        }

        // Preparar los datos de la notificación
        $link = 'clients/' . $clientId;
        if ($stationId) {
            $link .= '/' . $stationId;
        }

        $notificationData = [
            'icon' => 'mat_outline:history', // Puedes ajustar esto según sea necesario
            'image' => $image, // Puedes ajustar esto según sea necesario
            'title' => $model->name ?? 'Activity Updated',
            'description' => $importantChange ?? 'Activity Updated',
            'time' => now()->toIso8601String(),
            'link' => $link,
            'useRouter' => true,
        ];

        // Enviar la notificación a todos los administradores
        $admins = User::where('role', 'admin')->get();
        try {
            Notification::send($admins, new ActivityNotification($notificationData));

            // Crear una entrada en la tabla user_notifications para cada administrador
            foreach ($admins as $admin) {
                UserNotification::create([
                    'user_id' => $admin->id,
                    'notification_id' => $admin->notifications()->latest()->first()->id,
                    'is_read' => false,
                    'is_deleted' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error sending notification to admins: ' . $e->getMessage());
        }
    }




    public function logCreateActivity($model, $userId, Request $request)
    {
        // Determinar station_id y client_id
        $stationId = $model instanceof Station ? $model->id : null;
        $clientId = $model instanceof Client ? $model->id : ($model->client_id ?? null);

        // Registrar la actividad
        Activity::create([
            'user_id' => $userId,
            'model_type' => class_basename($model),
            'model_id' => $model->id,
            'station_id' => $stationId,
            'client_id' => $clientId,
            'action' => 'created',
            'description' => class_basename($model) . ' created.',
            'important_change' => null,
            'status' => null,
            'ticket_id' => null,
        ]);

        // Obtener el ID del usuario desde el token
        $userId = $this->getUserIdFromToken($request);
        $user = User::findOrFail($userId);

        // Determinar la imagen según el tipo de modelo
        if (class_basename($model) == 'Station') {
            $image = $model->image;
            $image = 'https://domintapi.com/images/station/' . $image;
        } elseif (class_basename($model) == 'Client') {
            $image = $model->image;
            $image = 'https://domintapi.com/images/client/' . $image;
        }

        // Preparar los datos de la notificación
        $notificationData = [
            'icon' => 'mat_outline:history', // Puedes ajustar esto según sea necesario
            'image' => $image, // Puedes ajustar esto según sea necesario
            'title' => $model->name ?? 'New ' . class_basename($model),
            'description' => class_basename($model) . ' created.',
            'time' => now()->toIso8601String(),
            'link' => $clientId ? 'clients/' . $clientId : ($stationId ? 'stations/' . $stationId : '/'),
            'useRouter' => true,
        ];

        // Enviar la notificación a todos los administradores
        $admins = User::where('role', 'admin')->get();
        try {
            Notification::send($admins, new ActivityNotification($notificationData));
        } catch (\Exception $e) {
            Log::error('Error sending notification to admins: ' . $e->getMessage());
        }
    }
    

    private function getUserIdFromToken(Request $request)
    {
        // Obtener el token desde el encabezado de autorización
        $token = $request->bearerToken();

        if (!$token) {
            throw new \Exception('Token not provided', 401);
        }

        // Decodificar el token para obtener el ID del usuario
        $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
        return $decoded->sub; // Asumiendo que el ID del usuario está en el campo 'sub'
    }

    public function logStationReport($station, $userId, $description, $importantChange, $status, $ticketId , Request $request)
    {
        // Registrar el reporte de la estación
        Activity::create([
            'user_id' => $userId,
            'model_type' => 'Station',
            'model_id' => $station->id,
            'station_id' => $station->id,
            'client_id' => $station->client_id ?? null,
            'action' => 'report',
            'description' => $description,
            'important_change' => $importantChange,
            'status' => $status,
            'ticket_id' => $ticketId,
        ]);

        // Obtener el ID del usuario desde el token
        $userId = $this->getUserIdFromToken($request);
        $user = User::findOrFail($userId);

        // Preparar los datos de la notificación
        $image = 'https://domintapi.com/images/station/' . $station->image;
        $notificationData = [
            'icon' => 'station-icon', // Puedes ajustar esto según sea necesario
            'image' => $image, // Puedes ajustar esto según sea necesario
            'title' => 'Station Report',
            'description' => $description,
            'time' => now()->toIso8601String(),
            'link' => 'stations/' . $station->id,
            'useRouter' => true,
        ];

        // Enviar la notificación a todos los administradores
        $admins = User::where('role', 'admin')->get();
        try {
            Notification::send($admins, new ActivityNotification($notificationData));
        } catch (\Exception $e) {
            Log::error('Error sending notification to admins: ' . $e->getMessage());
        }
    }

    public function logClientReport($client, $userId, $description, $importantChange, $status, $ticketId = null, Request $request)
    {
        // Registrar el reporte del cliente
        Activity::create([
            'user_id' => $userId,
            'model_type' => 'Client',
            'model_id' => $client->id,
            'station_id' => null,
            'client_id' => $client->id,
            'action' => 'report',
            'description' => $description,
            'important_change' => $importantChange,
            'status' => $status,
            'ticket_id' => $ticketId,
        ]);

        // Obtener el ID del usuario desde el token
        $userId = $this->getUserIdFromToken($request);
        $user = User::findOrFail($userId);

        // Preparar los datos de la notificación
        $image = 'https://domintapi.com/images/client/' . $client->image;
        $notificationData = [
            'icon' => 'client-icon', // Puedes ajustar esto según sea necesario
            'image' => $image, // Puedes ajustar esto según sea necesario
            'title' => 'Client Report',
            'description' => $description,
            'time' => now()->toIso8601String(),
            'link' => 'clients/' . $client->id,
            'useRouter' => true,
        ];

        // Enviar la notificación a todos los administradores
        $admins = User::where('role', 'admin')->get();
        try {
            Notification::send($admins, new ActivityNotification($notificationData));
        } catch (\Exception $e) {
            Log::error('Error sending notification to admins: ' . $e->getMessage());
        }
    }
}