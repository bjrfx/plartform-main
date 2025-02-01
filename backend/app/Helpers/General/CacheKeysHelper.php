<?php

namespace App\Helpers\General;

class CacheKeysHelper
{
    public static function getUserCacheKey(string $userId): string
    {
        return "user_$userId";
    }

    public static function getPaymentMerchantCacheKey(string $merchantId): string
    {
        return "payment_$merchantId";
    }

    public static function getCardConnectCacheKey(string $departmentId): string
    {
        return "card_connect_$departmentId";
    }

    /** @noinspection SpellCheckingInspection */
    public static function getPayaCacheKey(string $departmentId): string
    {
        return "paya_$departmentId";
    }

    public static function getDirectStatementKey(string $departmentId): string
    {
        return "direct_statement_$departmentId";
    }

    public static function getUrlQueryPayKey(string $departmentId): string
    {
        return "url_query_pay_$departmentId";
    }

    public static function getTylerKey(string $departmentId): string
    {
        return "tyler_$departmentId";
    }

    public static function getDepartment(string $departmentId): string
    {
        return "department_$departmentId";
    }

    public static function getDepartments(string $merchantId): string
    {
        return "merchant_{$merchantId}_departments";
    }
}
