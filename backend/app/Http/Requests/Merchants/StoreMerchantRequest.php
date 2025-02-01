<?php

namespace App\Http\Requests\Merchants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMerchantRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('merchants')->ignore(optional($this->route('merchant'))->id),
            ],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'fax' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048', // Logo must be an image
            'time_zone' => 'required|string',
            'is_enabled' => 'boolean',
            'is_bulk_notifications_enabled' => 'boolean',
            'is_payment_service_disabled' => 'boolean',
        ];
    }
}
