<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class FeesRequest extends FormRequest
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
            'token.token' => ['sometimes', 'string'],
            'cart.*' => ['required'],
            'cart.*.department_id' => ['required', 'uuid'],
            'cart.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
