<?php

namespace App\Models;

use App\Models\Concerns\HasHorillaMeta;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id',
    'company_id',
    'department_id',
    'job_position_id',
    'job_role_id',
    'reporting_manager_id',
    'coach_id',
    'email',
    'work_mobile',
    'work_phone',
    'date_joining',
    'employment_type',
    'work_location',
    'working_hours',
    'timezone',
    'is_active',
])]
class EmployeeWorkInformation extends Model
{
    use HasFactory, HasHorillaMeta;

    protected function casts(): array
    {
        return [
            'date_joining' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(JobRole::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'coach_id');
    }
}
