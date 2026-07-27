<?php

namespace App\Http\Controllers;

use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'settings' => StoreSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'currency_code' => ['required', 'string', 'max:3'],
            'currency_symbol' => ['required', 'string', 'max:8'],
            'default_tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'receipt_footer_text' => ['nullable', 'string', 'max:500'],
        ]);

        StoreSetting::current()->update($data);

        return back()->with('status', 'Store settings updated.');
    }
}
