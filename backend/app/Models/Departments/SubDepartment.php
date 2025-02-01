<?php

namespace App\Models\Departments;

use App\Observers\Departments\SubDepartmentObserver;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubDepartment extends Model
{
    use HasFactory, HasUuids;

    protected static function boot(): void
    {
        parent::boot();

        static::observe(SubDepartmentObserver::class);
    }

    protected $table = 'sub_departments';
    protected $fillable = [
        'id',
        'department_id',
        'name',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
