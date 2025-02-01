<?php

namespace App\Http\Requests\Users;

use App\Enums\Users\UserRoleEnums;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\Rule;


class UserInternalRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User $user */
        $user = $this->user();
        return $user->getAttribute('role') !== UserRoleEnums::MEMBER;
    }

    /**
     * @noinspection SpellCheckingInspection
     *
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|string|max:255',
            'merchant_id' => 'nullable|string|max:255',
            'role' => [
                'required',
                'string',
                Rule::in(UserRoleEnums::values()),
                function ($attribute, $value, $fail) {
                    if (
                        !is_null($this->input('merchant_id'))
                        &&
                        in_array($value, [
                            UserRoleEnums::SYSTEM_ADMIN->value,
                            UserRoleEnums::ADMIN->value,
                            UserRoleEnums::SUPPORT->value,
                        ], true)
                    ) {
                        $fail('Unauthorized role level requested.');
                    }
                },
            ],
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
            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::requiredIf(fn() => $this->shouldApplyRoleBasedValidation()),
                function ($attribute, $value, $fail) {
                    if (!is_null($this->input('phone_country_code')) && is_null($value)) {
                        $fail('Phone country code is required when phone is provided.');
                    }
                },
            ],
            'phone_country_code' => [
                'nullable',
                'string',
                'max:10',
                Rule::requiredIf(fn() => $this->shouldApplyRoleBasedValidation()),
                function ($attribute, $value, $fail) {
                    if (!is_null($this->input('phone')) && is_null($value)) {
                        $fail('Phone is required when phone country code is provided.');
                    }
                },
            ],
            'street' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $this->shouldApplyRoleBasedValidation()),
            ],
            'city' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $this->shouldApplyRoleBasedValidation()),
            ],
            'state' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => $this->shouldApplyRoleBasedValidation()),
            ],
            'zip_code' => [
                'nullable',
                'string',
                'max:10',
                Rule::requiredIf(fn() => $this->shouldApplyRoleBasedValidation()),
            ],
            'is_ebilling_enabled' => 'boolean',
            'password' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                RulesPassword::default(),  // Enforce password complexity
            ],
            'department_ids.*' => [
                'sometimes',
                'uuid'
            ]
        ];
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

    protected function prepareForValidation(): void
    {
        //Since a select option cannot be without value "null" as string was used
        if ($this->input('merchant_id') === 'null') {
            $this->merge([
                'merchant_id' => null
            ]);
        }
    }

    /**
     * Helper method to check if validation should apply.
     */
    protected function shouldApplyRoleBasedValidation(): bool
    {
        $role = $this->input('role');
        return $role === UserRoleEnums::MEMBER->value || is_null($role) || strlen($role) === 0;
    }
}
