<?php

namespace App\Http\Controllers;

use App\Models\MasterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterSettingController extends Controller
{
    private const INDIA_DOCUMENT_TYPES = [
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

    public function edit(): View
    {
        $settings = MasterSetting::firstOrCreate([]);
        if (empty($settings->employee_document_types)) {
            $settings->employee_document_types = self::INDIA_DOCUMENT_TYPES;
            $settings->save();
        }

        return view('settings.master', [
            'settings' => $settings,
            'currencies' => ['INR', 'USD', 'EUR', 'GBP', 'AED', 'SAR'],
            'documentTypesText' => implode(PHP_EOL, $settings->employee_document_types ?? []),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_currency_code' => ['required', 'string', 'max:10'],
            'employee_document_types_text' => ['nullable', 'string'],
        ]);

        $types = collect(preg_split('/\r\n|\r|\n/', (string) ($data['employee_document_types_text'] ?? '')))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();

        MasterSetting::firstOrCreate([])->update([
            'default_currency_code' => $data['default_currency_code'],
            'employee_document_types' => empty($types) ? self::INDIA_DOCUMENT_TYPES : $types,
        ]);

        return back()->with('status', 'Master settings saved.');
    }
}
