<?php

namespace App\Http\Resources\Forms;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostedPaymentsDefaultFieldsResource extends JsonResource
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
            'id' => $item?->getAttribute('field_id'),
            'default_label' => $item?->getAttribute('default_label'),
            'custom_label' => $item?->getAttribute('custom_label'),
            'is_required' => $item?->getAttribute('is_required'),
            'display_order' => $item?->getAttribute('display_order'),
            'type' => $item?->getAttribute('type'),
        ];
    }
}
