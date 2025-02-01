<?php /** @noinspection SpellCheckingInspection */

namespace App\Http\Requests\Gateway;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UrlQueryPayRequest extends FormRequest
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
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'amount_due' => 'required|string|max:255',
            'bill_payer_id' => 'required|string|max:255',
            'bill_number' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
