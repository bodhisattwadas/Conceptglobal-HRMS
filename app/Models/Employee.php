<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'badge_id',
    'profile_photo_url',
    'card_color',
    'first_name',
    'last_name',
    'email',
    'phone',
    'gender',
    'date_of_birth',
    'qualification',
    'experience_years',
    'marital_status',
    'children_count',
    'emergency_contact_name',
    'emergency_contact',
    'emergency_contact_relation',
    'address',
    'country',
    'state',
    'city',
    'zip',
    'is_active',
    'additional_info',
])]
class Employee extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'experience_years' => 'integer',
            'children_count' => 'integer',
            'is_active' => 'boolean',
            'additional_info' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workInformation(): HasOne
    {
        return $this->hasOne(EmployeeWorkInformation::class);
    }

    public function bankDetail(): HasOne
    {
        return $this->hasOne(EmployeeBankDetail::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name ?? '', 0, 1));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
