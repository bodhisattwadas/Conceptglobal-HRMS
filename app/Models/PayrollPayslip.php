<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPayslip extends Model
{
    protected $fillable = ['payroll_payslip_batch_id','employee_id','reference','name','date_from','date_to','status'];
    protected function casts(): array { return ['date_from' => 'date', 'date_to' => 'date']; }
    public function batch(): BelongsTo { return $this->belongsTo(PayrollPayslipBatch::class, 'payroll_payslip_batch_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
