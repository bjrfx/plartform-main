<?php

namespace App\Http\Requests\Gateway;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GatewayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['nullable', 'uuid'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gateways', 'name')->ignore($this->id, 'id')
            ],
            'base_url' => ['required', 'url'],
            'alternate_url' => ['sometimes', 'nullable', 'url'],
        ];
    }
}
