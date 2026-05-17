<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_loan_id',
    'installment_no',
    'payment_date',
    'amount',
    'paid_amount',
    'remaining_amount',
    'status',
    'paid_date',
])]
class EmployeeLoanInstallment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'paid_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(EmployeeLoan::class, 'employee_loan_id');
    }
}
