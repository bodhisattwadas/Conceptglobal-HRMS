<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollContract extends Model
{
    protected $fillable = [
        'employee_id','department_id','job_position_id','contract_name','start_date','end_date',
        'notice_period_days','employee_category','salary_structure','salary_structure_type',
        'working_schedule','hr_responsible','state','notes',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function jobPosition(): BelongsTo { return $this->belongsTo(JobPosition::class); }
}
