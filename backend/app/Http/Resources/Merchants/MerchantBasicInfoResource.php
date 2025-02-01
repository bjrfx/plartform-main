<?php

namespace App\Http\Resources\Merchants;

use App\Models\Merchants\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantBasicInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Merchant $merchant */
        $merchant = $this->resource;

        $logo = $merchant->getAttribute('logo');
        return [
            'id' => $merchant->getKey(),
            'name' => $merchant->getAttribute('name'),
            'subdomain' => $merchant->getAttribute('subdomain'),
            'logo' => $logo ? asset("storage/$logo") : null,
            'is_enabled' => $merchant->getAttribute('is_enabled'),
            'is_bulk_notifications_enabled' => $merchant->getAttribute('is_bulk_notifications_enabled'),
        ];
    }
}
