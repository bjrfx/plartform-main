<?php

namespace App\Services\Merchants;

use App\Models\Merchants\Merchant;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class MerchantService
{
    public function all(): Collection
    {
        return Merchant::query()->orderBy('name')->get();
    }
    public function get(string $id): Merchant
    {
        /** @var Merchant $merchant */
        $merchant = Merchant::query()
            ->with(['departments' => function (Builder $query) {
                $query->with('icon')
                    ->orderBy('display_order');
            }])
            ->where('id', $id)
            ->firstOrFail();
        return $merchant;
    }
    public function edit(Merchant $merchant): Merchant
    {
        $merchant->load(['departments' => function (Builder $query) {
                $query->with('icon')
                    ->orderBy('display_order');
            }]);

        return $merchant;
    }
    public function save(array $requestData, ?Merchant $merchant = null): Merchant
    {
        if(is_null($merchant)) {
            /** @var Merchant $merchant */
            $merchant = Merchant::query()->create($requestData);
        } else {
            $merchant->update($requestData);
        }

        return $merchant;
    }

    public function uploadLogo(Request $request, Merchant $merchant): void
    {
        if (!$request->hasFile('logo')) {
            return;
        }

        $logo = $merchant->getAttribute('logo');
        if(!is_null($logo)){
            Storage::disk('public')->delete($logo);
        }

        $merchantId = $merchant->getKey();

        // Generate a new filename based on the merchant ID
        $extension = $request->file('logo')->getClientOriginalExtension(); // e.g., jpg, png
        $fileName = "logo_$merchantId.$extension";

        // Store the file in the 'public/logos' directory with the new filename
        $request->file('logo')->storeAs('logos', $fileName, 'public');

        $merchant->setAttribute('logo', $fileName);
        $merchant->saveQuietly();
    }
}
