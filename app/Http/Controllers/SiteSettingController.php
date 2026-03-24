<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteSettingController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::first();

        return view('admin.settings.edit', compact('settings'));

    }

    public function update(Request $request)
    {

        $data = $request->validate([
            'home_enabled' => 'nullable|boolean',
            'login_enabled' => 'nullable|boolean',
            'register_enabled' => 'nullable|boolean',
            'nin_mod_enabled' => 'nullable|boolean',
        ]);

        // For each checkbox, set 1 if present, 0 if missing
        $data['home_enabled'] = $request->has('home_enabled') ? 1 : 0;
        $data['login_enabled'] = $request->has('login_enabled') ? 1 : 0;
        $data['register_enabled'] = $request->has('register_enabled') ? 1 : 0;
        $data['nin_mod_enabled'] = $request->has('nin_mod_enabled') ? 1 : 0;

        $settings = SiteSetting::firstOrNew();
        $settings->fill($data);
        $settings->nin_consent = $request->nin_consent;
        $settings->bvn_consent = $request->bvn_consent;
        $settings->save();

        Cache::forget('site-settings');

        return back()->with('success', 'Settings updated successfully');
    }
}
