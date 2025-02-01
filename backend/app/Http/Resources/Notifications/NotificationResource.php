<?php

namespace App\Http\Resources\Notifications;


use App\Models\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Notification $item */
        $item = $this->resource;
        return [
            'id' => $item?->getKey(),
            'merchant_id' => $item?->getAttribute('merchant_id'),
            'subject' => $item?->getAttribute('subject'),
            'type' => $item?->getAttribute('type'),
            'tested' => (bool)$item?->getAttribute('tested'),
            'sent' => (bool)$item?->getAttribute('sent'),
            'sent_count' => $item?->getAttribute('sent_count'),
            'enabled' => (bool)$item?->getAttribute('enabled'),

            'body' => $this->when($item?->relationLoaded('body'), function () use ($item) {
                return $item->getRelationValue('body')->getAttribute('body');
            }),
        ];
    }
}
