<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSalaryStructure extends Model
{
    protected $fillable = ['name', 'reference', 'salary_rules_count'];
}
