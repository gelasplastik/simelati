<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::active();

        return view('admin.settings', compact('setting'));
    }

    public function update(UpdateSettingRequest $request)
    {
        $setting = Setting::active();
        $setting->update([
            ...$request->validated(),
            'izin_requires_approval' => $request->boolean('izin_requires_approval'),
            'journal_lock_enabled' => $request->boolean('journal_lock_enabled'),
        ]);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
