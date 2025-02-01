<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\Rule;


class RegisterRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'id' => 'nullable|string|max:255',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')
                    ->where(function ($query) {
                        if (is_null($this->input('merchant_id'))) {
                            $query->whereNull('merchant_id');
                        } else {
                            $query->where('merchant_id', $this->input('merchant_id'));
                        }
                    })
                    ->ignore($this->input('id')), // Ignore current user on update
            ],
            'merchant_id' => 'nullable|string|max:255',
            'phone' => 'required|string|max:15',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'is_ebilling_enabled' => 'boolean',
        ];

        if (auth()->guest()) {
            $rules['password'] = [
                'bail',
                'required',
                'string',
                'confirmed',
                'max:255',
                RulesPassword::default()
            ];
        }

        return $rules;
    }

    /**
     * Customize error messages for validation.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Provide a valid email address.',
            'email.unique' => 'Email could not be processed. Please try again.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }
}
