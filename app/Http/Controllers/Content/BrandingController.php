<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** The two logos: one for light mode, one for dark mode (admin/branding). The website and the apps pick the right one. */
class BrandingController extends Controller
{
    public function index(Request $request)
    {
        StaffAccess::authorize($request, 'Content manage');

        return view('admin.content.branding', ['light' => SiteSetting::get('logo_light'), 'dark' => SiteSetting::get('logo_dark'), 'lightUrl' => SiteSetting::logo('light'), 'darkUrl' => SiteSetting::logo('dark')]);
    }

    public function save(Request $request)
    {
        StaffAccess::authorize($request, 'Content manage');
        $request->validate(['logo_light' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048', 'logo_dark' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:2048']);
        foreach (['light', 'dark'] as $mode) {
            $key = 'logo_' . $mode;
            $old = SiteSetting::get($key);
            if ($request->hasFile($key)) {
                $f = $request->file($key);
                $name = 'branding/' . $mode . '-' . Str::random(12) . '.' . $f->extension();
                $f->storeAs('files', $name, 'public');
                SiteSetting::put($key, $name);
            } elseif ($request->boolean('remove_' . $mode)) {
                SiteSetting::put($key, null);
            } else {
                continue;
            }
            if ($old) {
                Storage::disk('public')->delete('files/' . $old);
            }
        }

        return back()->with('message', 'Logos saved');
    }
}
