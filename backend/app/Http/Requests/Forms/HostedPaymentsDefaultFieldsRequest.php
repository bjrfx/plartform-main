<?php /** @noinspection SpellCheckingInspection */

namespace App\Http\Requests\Forms;

use App\Enums\Forms\HostedPaymentsTypeEnums;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class HostedPaymentsDefaultFieldsRequest extends FormRequest
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
            'default' => [
                'present',
                'array',
                function ($attribute, $value, $fail) {
                    $this->validateRequiredTypes($value, $fail);
                },
            ],
            'default.*.id' => 'nullable|uuid',
            'default.*.label' => 'nullable|string|max:255',
            'default.*.type' => [
                'nullable',
                Rule::in(HostedPaymentsTypeEnums::values()),
            ],
            'default.*.is_required' => 'boolean',
        ];
    }

    /**
     * Custom logic to validate that REFERENCE and AMOUNT exist exactly once.
     */
    private function validateRequiredTypes(array $fields, callable $fail): void
    {
        // Extract all `type` values
        $types = Arr::pluck($fields, 'type');

        // Count occurrences of each type
        $typeCounts = array_count_values($types);

        // List of required types
        $requiredTypes = [HostedPaymentsTypeEnums::REFERENCE->value, HostedPaymentsTypeEnums::AMOUNT->value];

        // Validate required types
        foreach ($requiredTypes as $requiredType) {
            if (($typeCounts[$requiredType] ?? 0) !== 1) {
                $fail("$requiredType must exist exactly once.");
            }
        }
    }
}
