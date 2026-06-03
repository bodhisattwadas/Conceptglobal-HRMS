<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['timesheet_id', 'old_status', 'new_status', 'changed_by', 'reason', 'changed_at'])]
class TimesheetStatusLog extends Model
{
    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    public function timesheet(): BelongsTo
    {
        return $this->belongsTo(Timesheet::class);
    }
}
