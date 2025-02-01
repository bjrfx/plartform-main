<?php

namespace App\Models\Icons;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Icon extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'id',
        'name',
        'svg_code'
    ];

}
