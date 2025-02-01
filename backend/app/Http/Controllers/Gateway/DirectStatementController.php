<?php

namespace App\Http\Controllers\Gateway;

use App\Enums\Gateway\GatewayTypeEnums;
use App\Helpers\General\CacheKeysHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\DirectStatementRequest;
use App\Http\Resources\Gateway\DirectStatementResource;
use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class DirectStatementController extends Controller
{
    /**
     * @note For internal processing that returns an array
     * @param Department $department
     * @return array
     */
    public function getMap(Department $department): array
    {
        $key = CacheKeysHelper::getDirectStatementKey($department->getKey());
        return cache()->remember(
            $key,
            3600,
            function () use ($department) {
                $department->load('paymentDirectStatementGateway');

                return DirectStatementResource::make($department)->resolve();
            });
    }

    public function edit(Merchant $merchant, Department $department): DirectStatementResource
    {
        $department->load('paymentDirectStatementGateway');

        return DirectStatementResource::make(
            $department,
            $merchant->getAttribute('name'),
        );
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function save(DirectStatementRequest $request, string $merchantId, Department $department): JsonResponse
    {
        $requestData = $request->validated();

        $department->load('paymentDirectStatementGateway');

        $this->saveGateway(requestData: $requestData, department: $department);

        $key = CacheKeysHelper::getDirectStatementKey($department->getKey());
        cache()->forget($key);

        return response()->json([
            'success' => true,
        ]);
    }

    private function saveGateway(array $requestData, Department $department): void
    {
        $gateway = $department->getRelationValue('paymentDirectStatementGateway');

        $save = [
            'type' => GatewayTypeEnums::DIRECT_STATEMENT,
            'custom_url' => Arr::get($requestData, 'custom_url'),
            'is_active' => Arr::get($requestData, 'is_active'),
            'external_identifier' => Arr::get($requestData, 'token'),
        ];

        if (is_null($gateway)) {
            $department->gateways()->create($save);
        } else {
            $gateway->update($save);
        }
    }
}
