<?php

namespace App\Http\Resources\Payments;


use App\Adapters\Transactions\Dtos\ResponseDto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ResponseDto $item */
        $item = $this->resource;
        return [
            'department_id' => $item->department_id,
            "success" => $item->success,
            "reference_number" => $item->reference_number,
            "status_code" => $item->status_code,
            "status_message" => $item->status_message,
            "batch_id" => $item->batch_id,
            "expiry" => $item->expiry,
            "extra_data" => $item->extra_data,
        ];
    }
}
