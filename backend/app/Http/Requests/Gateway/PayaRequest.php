<?php /** @noinspection SpellCheckingInspection */

namespace App\Http\Requests\Gateway;

use App\Rules\AmountRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayaRequest extends FormRequest
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
            'gateway_id' => 'required|uuid',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'web_terminal_id' => 'required|string|max:255',
            'large_transaction_terminal_id' => 'required|string|max:255',
            'ccd_void_terminal_id' => 'required|string|max:255',
            'allow_guest_payment' => 'boolean',
            'is_active' => 'boolean',
            'fee_amount' => [
                'required',
                new AmountRule()
            ],
            'fee_amount_large' => [
                'required',
                new AmountRule()
            ],
            'fee_percentage' => [
                'required',
                new AmountRule()
            ],
        ];
    }
}
