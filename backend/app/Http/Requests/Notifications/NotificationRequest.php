<?php

namespace App\Http\Requests\Notifications;

use App\Rules\TextFromWysiwyg;
use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Implement authorization logic if needed
        // Return true for simplicity (or implement user roles/permissions)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'merchant_id' => 'bail|nullable|uuid',
            'subject' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_null($this->input('merchant_id')) && is_null($value)) {
                        $fail('The subject is required.');
                    }
                },
                'string',
                'max:255'
            ],
            'body' => [
                'required',
                'string',
                new TextFromWysiwyg()
            ],
            'tested' => 'sometimes|boolean',
            'sent' => 'sometimes|boolean',
            'enabled' => 'sometimes|boolean',
        ];
    }

    /**
     * Customize error messages for specific fields.
     */
    public function messages(): array
    {
        return [
            'subject.required_with' => 'The subject is required.',
            'body.required' => 'The notification body cannot be empty.',
        ];
    }

}
