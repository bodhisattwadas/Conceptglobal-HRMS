<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = [
        'default_currency_code',
        'payroll_approval_required',
        'include_attendance_in_payroll',
        'include_leave_in_payroll',
        'include_timesheet_in_payroll',
        'default_working_days_per_month',
        'default_working_hours_per_day',
    ];

    protected function casts(): array
    {
        return [
            'payroll_approval_required' => 'boolean',
            'include_attendance_in_payroll' => 'boolean',
            'include_leave_in_payroll' => 'boolean',
            'include_timesheet_in_payroll' => 'boolean',
            'default_working_days_per_month' => 'decimal:2',
            'default_working_hours_per_day' => 'decimal:2',
        ];
    }
}
