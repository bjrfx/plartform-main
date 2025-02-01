<?php

namespace App\Http\Requests\Checkout;

use App\Enums\Billings\PaymentMethodTypesEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
            'payer.terms_and_conditions' => 'accepted', // Validate checkbox is checked
            'payer.id' => 'nullable|string|max:255',
            'payer.first_name' => 'required|string|max:255',
            'payer.middle_name' => 'nullable|string|max:255',
            'payer.last_name' => 'required|string|max:255',
            'payer.email' => 'required|email',
            'payer.phone' => 'required|string|max:15',
            'payer.phone_country_code' => 'required|string|max:10',
            'payer.address' => 'required|string|max:255',
            'payer.address2' => 'nullable|string|max:255',
            'payer.city' => 'required|string|max:255',
            'payer.state' => 'required|string|max:255',
            'payer.zip_code' => 'required|string|max:10',
            'payer.card_owner' => [
                Rule::requiredIf(function () {
                    return $this->input('type') === PaymentMethodTypesEnums::CREDIT->value;
                }),
                'nullable',
                'string',
                'max:255'
            ],

            'hsn' => ['sometimes', 'string'],//device hardware serial number (HSN)
            'token.token' => ['sometimes', 'string'],
            'cart.*' => ['required'],
            'cart.*.department_id' => ['required', 'uuid'],
            'cart.*.amount' => ['required', 'numeric', 'min:0'],
            'fees' => ['present', 'array'],
            'fees.*.department_id' => ['required', 'uuid'],
            'fees.*.amount' => ['required', 'numeric', 'min:0'],
            'type' => ['sometimes', Rule::enum(PaymentMethodTypesEnums::class)],

            'ach.account_holder_type' => ['sometimes', 'string', 'max:255'],
            'ach.check_name' => ['sometimes', 'string', 'max:255'],
            'ach.account_type' => ['sometimes', 'string', 'max:255'],
            'ach.check_aba' => ['sometimes', 'string', 'max:9'],
            'ach.check_account' => ['sometimes', 'string', 'max:17', 'confirmed'],
            'ach.check_account_confirmation' => ['sometimes', 'string'],
        ];
    }
}
