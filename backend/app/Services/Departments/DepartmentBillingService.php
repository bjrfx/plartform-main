<?php

namespace App\Services\Departments;

use App\Models\Departments\Department;
use App\Models\Merchants\Merchant;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class DepartmentBillingService
{
    protected ?Merchant $merchant = null;

    public function __construct()
    {
        $this->merchant = context('merchant');
    }

    /**
     * @throws ValidationException
     */
    public function get(string $slug): Department
    {
        $department = $this->getDepartment(slug: $slug);

        if (is_null($department)) {
            throw ValidationException::withMessages([
                'department_slug' => "Invalid department slug: $slug"
            ]);
        }

        return $department;
    }


    private function getDepartment(string $slug): ?Department
    {
        $isGuest = auth()->guest();
        $this->merchant->load(['departments' => function (Builder $query) use ($slug, $isGuest) {
            $query->scopes([
                'isVisible'
            ])
                ->where('slug', $slug)
                ->when($isGuest, function (Builder $query) {
                    $query->where('is_public', 1);
                })
                ->with('icon');
        }]);

        return $this->merchant->getRelationValue('departments')->first();
    }
}
