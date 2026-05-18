<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'loan_number',
    'employee_id',
    'department_id',
    'job_position_id',
    'company_id',
    'request_date',
    'loan_amount',
    'number_of_installments',
    'payment_start_date',
    'currency_code',
    'treasury_account',
    'loan_account',
    'journal',
    'status',
    'total_amount',
    'total_paid_amount',
    'balance_amount',
    'reason',
    'notes',
    'refusal_reason',
    'submitted_at',
    'submitted_by',
    'submitted_ip',
    'approved_at',
    'approved_by',
    'approved_ip',
    'refused_at',
    'refused_by',
    'refused_ip',
    'cancelled_at',
    'cancelled_by',
    'cancelled_ip',
])]
class EmployeeLoan extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'payment_start_date' => 'date',
            'loan_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'total_paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'refused_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(EmployeeLoanInstallment::class);
    }
}
