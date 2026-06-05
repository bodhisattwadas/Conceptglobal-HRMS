<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

#[Fillable([
    'company_id',
    'employee_id',
    'department_id',
    'project_id',
    'project_task_id',
    'date',
    'start_time',
    'end_time',
    'hours_spent',
    'timer_elapsed_seconds',
    'timer_logs',
    'description',
    'is_billable',
    'status',
    'submitted_at',
    'submitted_by',
    'approved_at',
    'approved_by',
    'rejected_at',
    'rejected_by',
    'rejection_reason',
    'source',
    'desktop_uuid',
    'desktop_submitted_machine_ip',
    'desktop_submitted_machine_mac',
])]
class Timesheet extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hours_spent' => 'decimal:2',
            'timer_elapsed_seconds' => 'integer',
            'timer_logs' => 'array',
            'is_billable' => 'boolean',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TimesheetStatusLog::class);
    }

    public function scopeDesktopSynced(Builder $query): Builder
    {
        if (! Schema::hasColumn('timesheets', 'desktop_uuid')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('source', 'desktop')->whereNotNull('desktop_uuid');
    }
}
