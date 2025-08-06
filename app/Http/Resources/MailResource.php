<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $additionalData = [];

        // Check if the mail is related to a station or client
        if ($this->station) {
            $additionalData = [
                'name' => $this->station->name,
                'status' => $this->station->status,
                'image' => $this->station->image,
            ];
        } elseif ($this->client) {
            $additionalData = [
                'name' => $this->client->name,
                'status' => $this->client->status,
                'image' => $this->client->image,
            ];
        }

        // Fetch and sort messages by 'created_at' in descending order
        $messages = $this->messages()->orderBy('created_at', 'desc')->get();

        return array_merge(parent::toArray($request), [
            'service' => $additionalData,
            'messages' => MailMessageResource::collection($messages),
            'client' => $this->client,
        ]);
    }
}
