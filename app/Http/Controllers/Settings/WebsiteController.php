<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebsiteController extends Controller
{
    public function index()
    {
        $setting = Setting::current();
        return view('settings.website', compact('setting'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['nullable', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'site_name' => $validated['site_name'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo_path'] = $path;
        }

        $setting = Setting::current();
        if ($setting) {
            // Xóa logo cũ nếu có và upload mới
            if (isset($data['logo_path']) && $setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $setting->update($data);
        } else {
            $setting = Setting::create($data);
        }

        return redirect()->route('settings.website')->with('success', 'Đã lưu thiết lập website.');
    }
}
