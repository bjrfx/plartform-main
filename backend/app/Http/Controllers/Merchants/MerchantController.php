<?php

namespace App\Http\Controllers\Merchants;

use App\Helpers\General\DomainHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Merchants\StoreMerchantRequest;
use App\Http\Resources\Merchants\MerchantBasicInfoResource;
use App\Http\Resources\Merchants\MerchantLayoutResource;
use App\Http\Resources\Merchants\MerchantResource;
use App\Models\Merchants\Merchant;
use App\Models\User;
use App\Services\Merchants\MerchantService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class MerchantController extends Controller
{
    protected ?string $id = null;

    public function __construct(
        protected MerchantService $merchantService
    )
    {
        // Get the merchantId from the request (injected by middleware)
        $this->id = request()->input('merchant_id');
    }

    public function index(): AnonymousResourceCollection
    {

        $merchants = $this->merchantService->all();

        return MerchantBasicInfoResource::collection($merchants);
    }

    public function edit(Merchant $merchant): MerchantResource
    {
        $merchant = $this->merchantService->edit(merchant: $merchant);
        return new MerchantResource($merchant);
    }

    public function get(): MerchantResource
    {
        $merchant = $this->merchantService->get(id: $this->id);

        return new MerchantResource($merchant);
    }

    public function save(StoreMerchantRequest $request, ?Merchant $merchant = null): MerchantResource
    {
        $validatedData = $request->validated();

        $merchant = $this->merchantService->save(requestData: $validatedData, merchant: $merchant);

        $this->merchantService->uploadLogo(request: $request, merchant: $merchant);

        return new MerchantResource($merchant);
    }

    /**
     */
    public function layout(): MerchantLayoutResource
    {
        if (is_null($this->id)) {
            //platform
            // Dynamically create a new Merchant instance
            $merchant = new Merchant([
                'id' => null,
                'name' => 'ReCo Anywhere',
                'logo' => '',
            ]);
        } else {
            $merchant = DomainHelper::getMerchant();
        }

        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        return MerchantLayoutResource::make($merchant, $user);
    }
}
