<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'notification_id' => $this->notification_id,
            'is_read' => $this->is_read,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'notification' => $this->notification ? [
                'type' => $this->notification->type,
                'data' => $this->notification->data,
                'read_at' => $this->notification->read_at,
                'created_at' => $this->notification->created_at,
                'updated_at' => $this->notification->updated_at,
            ] : null,
        ];
    }
}