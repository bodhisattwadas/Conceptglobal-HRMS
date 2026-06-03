<?php

namespace App\Services;

use App\Models\ProjectTask;

class TimesheetProgressService
{
    public function recalculateTask(ProjectTask $task): void
    {
        $spent = (float) $task->timesheets()
            ->whereIn('status', ['draft', 'submitted', 'approved'])
            ->sum('hours_spent');

        $planned = (float) $task->planned_hours;
        $task->update([
            'spent_hours' => $spent,
            'remaining_hours' => max($planned - $spent, 0),
            'extra_hours' => max($spent - $planned, 0),
            'progress_percent' => $planned > 0 ? min(($spent / $planned) * 100, 100) : 0,
        ]);
    }
}
