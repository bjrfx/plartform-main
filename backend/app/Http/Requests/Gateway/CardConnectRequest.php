<?php

namespace App\Http\Requests\Gateway;

use App\Rules\AmountRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CardConnectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Allow all users for now; modify as needed for authorization logic.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'gateway_id' => 'required|uuid',
            'merchant_username' => 'required|string|max:255',
            'merchant_password' => 'required|string|max:255',
            'merchant_mid' => 'required|string|max:255',
            'fee_username' => 'required|string|max:255',
            'fee_password' => 'required|string|max:255',
            'fee_mid' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
            'has_same_fee' => 'boolean',
            'credit_card_min' => [
                'required',
                new AmountRule()
            ],
            'credit_card_amount' => [
                'required',
                new AmountRule()
            ],
            'credit_card_percentage' => [
                'required',
                new AmountRule()
            ],
            'debit_card_min' => [
                'required_if:has_same_fee,false',
                'nullable',
                new AmountRule()
            ],
            'debit_card_amount' => [
                'required_if:has_same_fee,false',
                'nullable',
                new AmountRule()
            ],
            'debit_card_percentage' => [
                'required_if:has_same_fee,false',
                'nullable',
                new AmountRule()
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'has_same_fee' => $this->boolean('has_same_fee'),
        ]);
    }
}
