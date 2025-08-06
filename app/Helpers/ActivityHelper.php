<?php
namespace App\Helpers;

use App\Models\Activity;

class ActivityHelper
{
    public static function log($modelType, $modelId, $action, $description = null, $importantChange = null, $userId, $status = null)
    {
        // Extraer solo el nombre del modelo
        $modelType = class_basename($modelType);

        Activity::create([
            'user_id' => $userId,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action' => $action,
            'description' => $description,
            'important_change' => $importantChange,
            'status' => $status,
        ]);
    }
}