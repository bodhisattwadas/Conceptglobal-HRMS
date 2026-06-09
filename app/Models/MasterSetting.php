<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['default_currency_code', 'employee_document_types'])]
class MasterSetting extends Model
{
    use HasFactory;

    public const DEFAULT_EMPLOYEE_DOCUMENT_TYPES = [
        'Aadhaar Card',
        'PAN Card',
        'Passport',
        'Voter ID',
        'Driving License',
        'UAN Card',
        'ESIC Card',
        'Employment Contract',
        'Offer Letter',
        'Experience Certificate',
    ];

    protected function casts(): array
    {
        return [
            'employee_document_types' => 'array',
        ];
    }
}
