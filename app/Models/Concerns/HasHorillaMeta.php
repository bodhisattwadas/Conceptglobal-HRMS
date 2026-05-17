<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasHorillaMeta
{
    protected static function bootHasHorillaMeta(): void
    {
        static::creating(function ($model): void {
            if (Auth::check()) {
                $model->created_by_id ??= Auth::id();
                $model->modified_by_id = Auth::id();
            }
        });

        static::updating(function ($model): void {
            if (Auth::check()) {
                $model->modified_by_id = Auth::id();
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
