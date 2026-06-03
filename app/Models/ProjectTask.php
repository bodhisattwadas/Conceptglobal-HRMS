<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'project_id',
    'parent_task_id',
    'title',
    'description',
    'deadline',
    'planned_hours',
    'spent_hours',
    'remaining_hours',
    'extra_hours',
    'progress_percent',
    'status',
    'is_recurrent',
    'priority',
])]
class ProjectTask extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'planned_hours' => 'decimal:2',
            'spent_hours' => 'decimal:2',
            'remaining_hours' => 'decimal:2',
            'extra_hours' => 'decimal:2',
            'progress_percent' => 'decimal:2',
            'is_recurrent' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_task_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'project_task_assignees')->withTimestamps();
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }
}
