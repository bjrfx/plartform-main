<?php

namespace App\Observers\Merchants;

use App\Models\Merchants\Merchant;

class MerchantObserver
{
    public function updated(Merchant $merchant): void
    {
        $subdomain = $merchant->getAttribute('subdomain');
        //Reset the subdomain middleware cache
        cache()->forget("subdomain_{$subdomain}");
    }
}
