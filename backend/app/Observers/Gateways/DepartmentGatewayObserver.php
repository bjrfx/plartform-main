<?php

namespace App\Observers\Gateways;

use App\Helpers\General\CacheKeysHelper;
use App\Models\Gateway\DepartmentGateway;


class DepartmentGatewayObserver
{
    public function saved(DepartmentGateway $departmentGateway): void
    {
        $key = CacheKeysHelper::getPayaCacheKey($departmentGateway->getAttribute('department_id'));
        cache()->forget($key);

        $departmentGateway->loadMissing('department');
        $merchantId = $departmentGateway->getRelationValue('department')->getAttribute('merchant_id');
        $key = CacheKeysHelper::getPaymentMerchantCacheKey($merchantId);
        cache()->forget($key);
    }

}
