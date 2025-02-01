<?php /** @noinspection SpellCheckingInspection */

namespace App\Http\Requests\Gateway;

use Illuminate\Foundation\Http\FormRequest;

class TylerRequest extends FormRequest
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
            'jur' => 'required|string|max:255',
            'flag_of_current_due' => 'required|string|max:255',
            'cycle_dues' => 'required',
            'cycle_dues.*' => 'required|date',
            'is_active' => 'nullable|boolean',
            'restriction' => 'present|array',
            'restriction.*.key' => 'required|string|max:255',
            'restriction.*.values' => 'required|string|max:255',
            'restriction.*.disabled_alert' => 'required|string|max:255',
            'restriction.*.enabled' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $restrictions = $this->restriction ?? [];
        foreach ($restrictions as &$restriction) {
            if (is_numeric($restriction['enabled'])) {
                $restriction['enabled'] = $restriction > 0;
            }
        }
        $this->merge([
            'restriction' => $restrictions,
        ]);
    }
}
