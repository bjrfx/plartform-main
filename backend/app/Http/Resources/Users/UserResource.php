<?php

namespace App\Http\Resources\Users;

use App\Helpers\General\PhoneFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property User $resource
 */
class UserResource extends JsonResource
{
    /**
     * @noinspection SpellCheckingInspection
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'merchant_id' => $this->resource->getAttribute('merchant_id'),
            'name' => $this->resource->getAttribute('name'),
            'email' => $this->resource->getAttribute('email'),
            'first_name' => $this->resource->getAttribute('first_name'),
            'middle_name' => $this->resource->getAttribute('middle_name'),
            'last_name' => $this->resource->getAttribute('last_name'),
            'phone_country_code' => $this->resource->getAttribute('phone_country_code'),
            'phone' => $this->resource->getAttribute('phone'),
            'street' => $this->resource->getAttribute('street'),
            'city' => $this->resource->getAttribute('city'),
            'state' => $this->resource->getAttribute('state'),
            'zip_code' => $this->resource->getAttribute('zip_code'),
            'role' => $this->resource->getAttribute('role'),
            'is_enabled' => $this->resource->getAttribute('is_enabled'),
            'profile_updated_at' => $this->resource->getAttribute('profile_updated_at'),
            'is_ebilling_enabled' => $this->resource->getAttribute('is_ebilling_enabled'),
            'ebilling_opt_at_tz' => $this->resource->getAttribute('ebilling_opt_at_tz'),
            'is_notifications_enabled' => $this->resource->getAttribute('is_notifications_enabled'),
            'is_card_payment_only' => $this->resource->getAttribute('is_card_payment_only'),
            'only_card_payment_updated_at_tz' => $this->resource->getAttribute('only_card_payment_updated_at_tz'),
            'merchant' => $this->when($this->resource->relationLoaded('merchant'), function () {
                return $this->resource->getRelationValue('merchant')?->getAttribute('name');
            }),
            'formatted_phone_number' => $this->resource->getAttribute('formatted_phone_number'),
            'department_ids' => $this->resource->getRelationValue('departments')->pluck('id')->all(),
        ];
    }
}
