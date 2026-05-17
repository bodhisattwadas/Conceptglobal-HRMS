<?php

namespace App\Models;

use App\Models\Concerns\HasHorillaMeta;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['job_position_id', 'name', 'is_active'])]
class JobRole extends Model
{
    use HasFactory, HasHorillaMeta;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)->withTimestamps();
    }
}
