<?php
/** @noinspection SpellCheckingInspection */

namespace App\Http\Resources\Gateway;

use App\Models\Gateway\Gateway;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GatewayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Gateway $item */
        $item = $this->resource;

        return [
            'id' => $item?->getKey(),
            'name' => $item?->getAttribute('name'),
            'type' => $item?->getAttribute('type'),
            'base_url' => $item?->getAttribute('base_url'),
            'alternate_url' => $item?->getAttribute('alternate_url'),
        ];
    }
}
