<?php /** @noinspection SpellCheckingInspection */

namespace App\Http\Requests\Forms;

use App\Enums\Forms\HostedPaymentsTypeEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HostedPaymentsFieldsRequest extends FormRequest
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
            'default' => 'present|array',
            'default.*.id' => 'required|uuid',
            'default.*.label' => 'nullable|string|max:255',
            'default.*.custom_label' => 'sometimes|nullable|string|max:255',

            'custom' => 'present|array',
            'custom.*.id' => 'nullable|uuid',
            'custom.*.label' => 'required|string|max:255',
            'custom.*.type' => [
                'required',
                Rule::in(HostedPaymentsTypeEnums::values()),
            ],
        ];
    }
}
