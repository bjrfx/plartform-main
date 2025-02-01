<?php

namespace App\Http\Resources\Merchants;

use App\Helpers\General\DomainHelper;
use App\Http\Resources\Users\UserBasicInfoResource;
use App\Models\Merchants\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantLayoutResource extends JsonResource
{
    protected ?User $user = null;

    public function __construct($resource, $user = null)
    {
        parent::__construct($resource);
        $this->user = $user;
    }

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
            'platform_domain' => DomainHelper::getDomain(),
            'platform_subdomain' => DomainHelper::getSubDomain(),
            'id' => $merchant->getKey(),
            'name' => $merchant->getAttribute('name'),
            'address' => $merchant->getAttribute('address'),
            'city' => $merchant->getAttribute('city'),
            'state' => $merchant->getAttribute('state'),
            'zip' => $merchant->getAttribute('zip'),
            'phone' => $merchant->getAttribute('phone'),
            'fax' => $merchant->getAttribute('fax'),
            'logo' => $logo ? asset("storage/$logo") : null, // Return full logo URL
            'time_zone' => $merchant->getAttribute('time_zone'),
            'is_bulk_notifications_enabled' => $merchant->getAttribute('is_bulk_notifications_enabled'),
            'user' => is_null($this->user) ? null : new UserBasicInfoResource($this->user),
        ];
    }
}
