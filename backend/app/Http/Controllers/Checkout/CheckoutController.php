<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Requests\Checkout\FeesRequest;
use App\Http\Resources\Payments\PaymentTransactionResource;
use App\Services\Gateways\CardConnectService;
use App\Services\Gateways\PayaService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(
        protected PaymentService     $paymentService,
        protected CardConnectService $cardConnectService,
        protected PayaService        $payaService,
    )
    {
    }

    /**
     * @throws ValidationException
     */
    public function checkout(CheckoutRequest $request): AnonymousResourceCollection
    {
        $requestData = $request->validated();

        $result = $this->paymentService->handle(requestData: $requestData);

        return PaymentTransactionResource::collection($result);
    }


    /**
     * @throws ValidationException
     */
    public function iFrameSrc(Request $request): JsonResponse
    {
        $requestData = $request->validate([
            'department_ids.*' => ['required', 'uuid'],
        ]);

        $items = $this->cardConnectService->iFrameSrc(requestData: $requestData);

        return response()->json([
            'src' => $items->first()->getRelationValue('gateway')?->getAttribute('alternate_url'),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function cardFees(FeesRequest $request): JsonResponse
    {
        $requestData = $request->validated();

        $fees = $this->cardConnectService->fees(requestData: $requestData);
        return response()->json([
            'data' => $fees
        ]);
    }

    public function checkFees(FeesRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        $fees = $this->payaService->fees(requestData: $requestData);
        return response()->json([
            'data' => $fees
        ]);
    }
}
