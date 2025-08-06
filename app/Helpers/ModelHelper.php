<?php

namespace App\Helpers;

class ModelHelper
{
    public static function getModelData($modelType, $modelId)
    {
        $modelClass = 'App\\Models\\' . $modelType;
        if (class_exists($modelClass)) {
            return $modelClass::find($modelId);
        }
        return null;
    }
}