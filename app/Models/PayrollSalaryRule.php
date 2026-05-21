<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSalaryRule extends Model
{
    protected $fillable = [
        'name','code','sequence','active','appears_on_payslip',
        'condition_based_on','amount_type','python_code','contribution_register',
    ];
}
