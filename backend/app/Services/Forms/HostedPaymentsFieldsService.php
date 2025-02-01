<?php

namespace App\Services\Forms;

use App\Enums\Forms\HostedPaymentsTypeEnums;
use App\Models\Departments\Department;
use App\Models\Forms\HostedPayments;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class HostedPaymentsFieldsService
{
    private int $displayOrder = 0;

    public function getDefaultFields(): Collection
    {
        return HostedPayments::query()
            ->whereNull('department_id') // Fetch only default fields
            ->orderBy('display_order')
            ->get();
    }

    public function saveDefaultFields(array $requestData): void
    {
        $ids = [];
        $fields = Arr::get($requestData, 'default', []);
        foreach ($fields as $index => $field) {
            if ($field['type'] === HostedPaymentsTypeEnums::DIVIDER->value) {
                $field['label'] = '-' . HostedPaymentsTypeEnums::DIVIDER->value . '-';
                $field['is_required'] = false;
            }
            $item = HostedPayments::query()->updateOrCreate([
                'id' => $field['id'],
            ], [
                'label' => $field['label'],
                'type' => $field['type'],
                'is_required' => $field['is_required'],
                'display_order' => $index,
            ]);
            $ids[] = $item->getKey();
        }
        HostedPayments::query()->whereNotIn('id', $ids)->delete();
    }

    public function getDefaultFieldsDepartment(string $departmentId): Collection
    {
        $table = (new HostedPayments())->getTable();
        return HostedPayments::query()
            ->select([
                "$table.id AS field_id",
                "$table.label AS default_label",
                "override.label AS custom_label",
                "$table.is_required",
                "$table.type",
                "$table.display_order",
            ])
            ->leftJoin("$table AS override", function ($join) use ($table, $departmentId) {
                $join->on('override.parent_id', '=', "$table.id")
                    ->whereNull('override.deleted_at') //Soft Delete is not scoped on join
                    ->where('override.department_id', '=', $departmentId);
            })
            ->whereNull("$table.department_id")  // Fetch only default fields
            ->orderBy("$table.display_order")
            ->get();
    }

    public function getCustomFieldsDepartment(string $departmentId): Collection
    {
        return HostedPayments::query()
            ->where('department_id', $departmentId) // Fetch only custom fields
            ->whereNull('parent_id')
            ->orderBy('display_order')
            ->get();
    }

    public function save(array $requestData, Department $department): void
    {
        DB::transaction(function () use ($requestData, $department) {
            $this->setDefaultFieldsDepartment(requestData: $requestData, department: $department);
            $this->setCustomFieldsDepartment(requestData: $requestData, department: $department);
        });
    }

    private function setDefaultFieldsDepartment(array $requestData, Department $department): void
    {
        $defaultFields = Arr::get($requestData, 'default', []);
        foreach ($defaultFields as $field) {
            $id = Arr::get($field, 'id');
            $label = Arr::get($field, 'custom_label');

            /** @var HostedPayments $existingField */
            $existingField = HostedPayments::query()
                ->where('department_id', $department->getKey())
                ->where('parent_id', $id) // parent_id links to the default field
                ->first();

            if (!is_null($existingField)) {
                if (is_null($label)) {
                    //if Override was removed
                    $existingField->delete();
                } else {
                    $existingField->update([
                        'label' => $label,
                        'display_order' => $this->displayOrder,
                    ]);
                }
            } elseif (!is_null($label)) {
                $department->hostedPaymentsFormFields()->create([
                    'parent_id' => $id,
                    'label' => $label,
                    'display_order' => $this->displayOrder,
                ]);
            }
            $this->displayOrder++;
        }
    }

    private function setCustomFieldsDepartment(array $requestData, Department $department): void
    {
        $customFields = Arr::get($requestData, 'custom', []);
        foreach ($customFields as $field) {
            $id = Arr::get($field, 'id');
            $label = Arr::get($field, 'label');
            $existingField = HostedPayments::query()
                ->where('department_id', $department->getKey())
                ->where('id', $id)
                ->first();
            $save = [
                'label' => $label,
                'display_order' => $this->displayOrder,
            ];
            if ($existingField) {
                $existingField->update($save);
            } else {
                $department->hostedPaymentsFormFields()->create($save);
            }
            $this->displayOrder++;
        }
    }
}
