<?php

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
            'id' => 'nullable|uuid',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'icon_id' => 'nullable|string|max:255',
            'person_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Logo must be an image
            'is_enabled' => 'boolean',
            'is_visible' => 'boolean',
            'is_public' => 'is_public',
            'display_order' => 'nullable|numeric|min:0',
            'parent_id' => 'sometimes|nullable|uuid',
            'description' => 'sometimes|nullable|string|max:500',
            'amount' => 'sometimes|nullable|numeric|min:0',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments')
                    ->where(fn($query) => $query->where('merchant_id', $this->input('merchant_id')))
                    ->ignore($this->input('id')),
            ],
        ];
    }
}
