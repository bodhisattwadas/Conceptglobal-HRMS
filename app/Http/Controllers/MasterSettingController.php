<?php

namespace App\Http\Controllers;

use App\Models\MasterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.master', [
            'settings' => MasterSetting::firstOrCreate([]),
            'currencies' => ['INR', 'USD', 'EUR', 'GBP', 'AED', 'SAR'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_currency_code' => ['required', 'string', 'max:10'],
        ]);

        MasterSetting::firstOrCreate([])->update($data);

        return back()->with('status', 'Master settings saved.');
    }
}
