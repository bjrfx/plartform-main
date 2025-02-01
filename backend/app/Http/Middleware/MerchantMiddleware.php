<?php

namespace App\Http\Middleware;

use App\Helpers\General\DomainHelper;
use App\Models\Merchants\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class MerchantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use the subdomain from the route parameters
        $domain = $request->route('domain');
        $tld = $request->route('tld');
        $subdomain = $request->route('subdomain');

        $merchant = $this->getMerchant(subdomain: $subdomain);

        if (is_null($merchant)) {
            cache()->forget("merchant_$subdomain");

            return response()->json(['message' => 'Not Found'], 404);
        }

        $request->merge(['merchant_id' => $merchant->getKey()]);

        DomainHelper::setMerchant($merchant);
        DomainHelper::setDomain($domain, $tld);
        DomainHelper::setSubDomain($subdomain, $domain, $tld);

        //logger("PlatformMiddleware executed: {$domain}.{$tld}");

        // Remove them from route parameters to prevent them from being passed to the controller
        $request->route()->forgetParameter('subdomain');
        $request->route()->forgetParameter('domain');
        $request->route()->forgetParameter('tld');

        return $next($request);
    }

    private function getMerchant(string $subdomain): ?Merchant
    {
        return cache()->remember(
            "merchant_$subdomain",
            60 * 60,
            function () use ($subdomain) {
                /** @var Merchant $merchant */
                $merchant = Merchant::query()
                    ->where('subdomain', $subdomain)
                    ->first();
                return $merchant;
            }
        );
    }
}
