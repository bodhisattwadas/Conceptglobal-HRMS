<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'approval', 'color', 'is_paid', 'default_days'])]
class LeaveType extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'default_days' => 'decimal:2',
        ];
    }
}
