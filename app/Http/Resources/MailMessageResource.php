<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MailMessageResource extends JsonResource
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
            'mail_id' => $this->mail_id,
            'sender_id' => $this->sender_id,
            'recipient_id' => $this->recipient_id,
            'sender_type' => $this->sender_type,
            'message' => $this->message,
            'attachments' => $this->attachments,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}