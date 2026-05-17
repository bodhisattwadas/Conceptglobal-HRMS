<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email_alias_prefix', 'email_alias_domain', 'leave_reminder_enabled', 'leave_reminder_days_before', 'employee_shift_enabled', 'vacation_management_enabled'])]
class LeaveSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'leave_reminder_enabled' => 'boolean',
            'employee_shift_enabled' => 'boolean',
            'vacation_management_enabled' => 'boolean',
        ];
    }
}
