<?php

namespace App\Helpers\General;

use App\Models\Merchants\Merchant;
use Illuminate\Support\Facades\Context;

class DomainHelper
{
    public static function getMerchant(): ?Merchant
    {
        return Context::get('merchant');
    }

    public static function getDomain(): ?string
    {
        return Context::get('platform-domain');
    }

    public static function getSubDomain(): ?string
    {
        return Context::get('platform-subdomain');
    }

    public static function setMerchant(Merchant $merchant): void
    {
        Context::add('merchant', $merchant);
    }

    public static function setDomain(string $domain, string $tld): void
    {
        Context::add('platform-domain', $domain . '.' . $tld);
    }

    public static function setSubDomain(string $subdomain, string $domain, string $tld): void
    {
        Context::add('platform-subdomain', $subdomain . '.' . $domain . '.' . $tld);
    }
}
