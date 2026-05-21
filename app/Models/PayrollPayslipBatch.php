<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPayslipBatch extends Model
{
    protected $fillable = ['name','date_from','date_to','credit_note','state'];
    protected function casts(): array { return ['date_from' => 'date', 'date_to' => 'date', 'credit_note' => 'boolean']; }
    public function payslips(): HasMany { return $this->hasMany(PayrollPayslip::class); }
}
