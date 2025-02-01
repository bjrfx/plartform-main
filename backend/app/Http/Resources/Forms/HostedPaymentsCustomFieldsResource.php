<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostedPaymentsCustomFieldsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = $this->resource;

        return [
            'id' => $item?->getAttribute('id'),
            'label' => $item?->getAttribute('label'),
            'type' => $item?->getAttribute('type'),
            'is_required' => (bool)$item?->getAttribute('is_required'),
            'display_order' => $item?->getAttribute('display_order'),
        ];
    }
}
