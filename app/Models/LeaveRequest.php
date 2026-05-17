<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['employee_id', 'leave_type_id', 'company_id', 'from_date', 'to_date', 'duration_days', 'duration_hours', 'remaining_legal_leaves', 'description', 'status'])]
class LeaveRequest extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'from_date' => 'date',
            'to_date' => 'date',
            'duration_days' => 'decimal:2',
            'duration_hours' => 'decimal:2',
            'remaining_legal_leaves' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function pendingWorks(): HasMany
    {
        return $this->hasMany(LeavePendingWork::class);
    }
}
