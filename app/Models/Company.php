<?php

namespace App\Models;

use App\Models\Concerns\HasHorillaMeta;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'is_hq',
    'address',
    'country',
    'state',
    'city',
    'zip',
    'date_format',
    'time_format',
    'is_active',
])]
class Company extends Model
{
    use HasFactory, HasHorillaMeta;

    protected function casts(): array
    {
        return [
            'is_hq' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class)->withTimestamps();
    }
}
