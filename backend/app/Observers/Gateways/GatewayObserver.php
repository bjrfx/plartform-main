<?php

namespace App\Observers\Gateways;

use App\Helpers\General\CacheKeysHelper;
use App\Models\Gateway\DepartmentGateway;
use App\Models\Gateway\Gateway;


class GatewayObserver
{
    public function saved(Gateway $gateway): void
    {
        $gateway->load(['departmentCredentials' => function ($query) {
            $query->with('department');
        }]);
        
        $departmentCredentials = $gateway->getRelationValue('departmentCredentials');

        $merchantIds = [];
        foreach ($departmentCredentials as $departmentCredential) {
            $key = CacheKeysHelper::getPayaCacheKey($departmentCredential->getAttribute('department_id'));
            cache()->forget($key);

            $merchantId = $departmentCredential->getRelationValue('department')->getAttribute('merchant_id');
            $merchantIds[$merchantId] = $merchantId;
        }

        foreach ($merchantIds as $merchantId) {
            $key = CacheKeysHelper::getPaymentMerchantCacheKey($merchantId);
            cache()->forget($key);
        }
    }

}
