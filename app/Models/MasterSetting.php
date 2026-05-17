<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['default_currency_code', 'employee_document_types'])]
class MasterSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'employee_document_types' => 'array',
        ];
    }
}
