<?php /** @noinspection SpellCheckingInspection */

namespace App\Http\Requests\Gateway;

use Illuminate\Foundation\Http\FormRequest;

class DirectStatementRequest extends FormRequest
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
            'custom_url' => 'required|url:https|max:255',
            'token' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
