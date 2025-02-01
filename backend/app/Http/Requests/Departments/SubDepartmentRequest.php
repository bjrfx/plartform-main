<?php

namespace App\Http\Requests\Departments;

use Illuminate\Foundation\Http\FormRequest;

class SubDepartmentRequest extends FormRequest
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
            'label' => 'required|string|max:255',
            'subs' => 'required|array',
            'subs.*.id' => 'nullable|uuid',
            'subs.*.name' => 'required|string|max:255',
            'subs.*.is_active' => 'required|boolean',
        ];
    }
}
