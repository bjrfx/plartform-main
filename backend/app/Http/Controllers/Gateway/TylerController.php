<?php

namespace App\Http\Controllers\Gateway;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\TylerRequest;
use App\Http\Resources\Gateway\TylerResource;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class TylerController extends Controller
{
    /**
     * @note For internal processing that returns an array
     * @param Department $department
     * @return array
     */
    public function getMap(Department $department): array
    {
        $key = CacheKeysHelper::getTylerKey($department->getKey());
        return cache()->remember(
            $key,
            3600,
            function () use ($department) {
                $department->load('tylerGateway');

                return TylerResource::make($department)->resolve();
            });
    }

    public function edit(Merchant $merchant, Department $department): TylerResource
    {
        $department->load('tylerGateway');

        return TylerResource::make($department, $merchant->getAttribute('name'));
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function save(TylerRequest $request, string $merchantId, Department $department): JsonResponse
    {
        $requestData = $request->validated();

        $department->load('tylerGateway');

        $this->saveGateway(requestData: $requestData, department: $department);

        $key = CacheKeysHelper::getTylerKey($department->getKey());
        cache()->forget($key);

        return response()->json([
            'success' => true,
        ]);
    }

    private function saveGateway(array $requestData, Department $department): void
    {
        $gateway = $department->getRelationValue('tylerGateway');

        $additionalData = [
            'jur' => Arr::get($requestData, 'jur'),
            'flag_of_current_due' => Arr::get($requestData, 'flag_of_current_due'),
            'cycle_dues' => Arr::get($requestData, 'cycle_dues', []),
        ];

        $save = [
            'type' => GatewayTypeEnums::TYLER,
            'custom_url' => Arr::get($requestData, 'custom_url'),
            'is_active' => Arr::get($requestData, 'is_active') ?? false,
            'username' => Arr::get($requestData, 'username'),
            'password' => Arr::get($requestData, 'password'),
            'additional_data' => $additionalData,
        ];

        if (is_null($gateway)) {
            $department->gateways()->create($save);
        } else {
            $gateway->update($save);
        }
    }
}
