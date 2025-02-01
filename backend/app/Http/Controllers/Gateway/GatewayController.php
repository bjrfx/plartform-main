<?php

namespace App\Http\Controllers\Gateway;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\GatewayRequest;
use App\Http\Resources\Gateway\GatewayResource;
use App\Models\Gateway\Gateway;
use App\Services\Gateways\CardConnectService;
use App\Services\Gateways\PayaService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** @noinspection SpellCheckingInspection */

class GatewayController extends Controller
{
    public function __construct(
        protected CardConnectService $cardConnectService,
        protected PayaService        $payaService,
    )
    {
    }

    public function indexCardConnect(): AnonymousResourceCollection
    {
        $items = $this->cardConnectService->index();

        return GatewayResource::collection($items);
    }

    /** @noinspection SpellCheckingInspection */
    public function indexPaya(): AnonymousResourceCollection
    {
        $items = $this->payaService->index();

        return GatewayResource::collection($items);
    }

    public function edit(?Gateway $gateway = null): GatewayResource
    {
        return new GatewayResource($gateway);
    }

    public function saveCardConnect(GatewayRequest $request, ?Gateway $gateway = null): GatewayResource
    {
        $requestData = $request->validated();

        $gateway = $this->cardConnectService->save(requestData: $requestData, gateway: $gateway);

        return new GatewayResource($gateway);
    }

    /**
     * @noinspection SpellCheckingInspection
     */
    public function savePaya(GatewayRequest $request, ?Gateway $gateway = null): GatewayResource
    {
        $requestData = $request->validated();

        $gateway = $this->payaService->save(requestData: $requestData, gateway: $gateway);

        return new GatewayResource($gateway);
    }
}
