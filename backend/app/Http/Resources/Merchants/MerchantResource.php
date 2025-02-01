<?php

namespace App\Http\Resources\Merchants;

use App\Http\Resources\Departments\DepartmentResource;
use App\Models\Merchants\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
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
            'address' => $merchant->getAttribute('address'),
            'city' => $merchant->getAttribute('city'),
            'state' => $merchant->getAttribute('state'),
            'zip' => $merchant->getAttribute('zip'),
            'phone' => $merchant->getAttribute('phone'),
            'phone_code' => $merchant->getAttribute('phone_code'),
            'fax' => $merchant->getAttribute('fax'),
            'fax_code' => $merchant->getAttribute('fax_code'),
            'logo' => $logo ? asset("storage/$logo") : null, // Return full logo URL
            'time_zone' => $merchant->getAttribute('time_zone'),
            'is_enabled' => $merchant->getAttribute('is_enabled'),
            'is_bulk_notifications_enabled' => $merchant->getAttribute('is_bulk_notifications_enabled'),
            'is_payment_service_disabled' => $merchant->getAttribute('is_payment_service_disabled'),
            'departments' => $this->when($merchant->relationLoaded('departments'), DepartmentResource::collection($merchant->getRelationValue('departments'))),
        ];
    }
}
