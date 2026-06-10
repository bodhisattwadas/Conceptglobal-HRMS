<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'company_id',
    'allow_future_entries',
    'future_entry_limit_days',
    'allow_employee_edit_after_submit',
    'allow_employee_delete_after_submit',
    'require_approval',
    'minimum_hours_per_entry',
    'maximum_hours_per_day',
    'desktop_timer_timeout_seconds',
    'restrict_to_assigned_tasks',
    'lock_after_payroll',
])]
class TimesheetSetting extends Model
{
    protected function casts(): array
    {
        return [
            'allow_future_entries' => 'boolean',
            'allow_employee_edit_after_submit' => 'boolean',
            'allow_employee_delete_after_submit' => 'boolean',
            'require_approval' => 'boolean',
            'minimum_hours_per_entry' => 'decimal:2',
            'maximum_hours_per_day' => 'decimal:2',
            'desktop_timer_timeout_seconds' => 'integer',
            'restrict_to_assigned_tasks' => 'boolean',
            'lock_after_payroll' => 'boolean',
        ];
    }
}
