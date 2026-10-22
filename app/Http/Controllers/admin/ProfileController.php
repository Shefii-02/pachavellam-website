<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Profile of the logged in Admin / Admin Staff.
 * Everyone can change their name, photo and password.
 * Email and mobile are shown to everyone but only an Admin can change them.
 */
class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.profile', [
            'user' => $request->user(),
            'canEditContact' => $this->canEditContact($request),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        if ($this->canEditContact($request)) {
            $rules['email'] = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];
            $rules['mobile'] = ['nullable', 'regex:/^\+?[0-9 \-]{7,15}$/', Rule::unique('users', 'mobile')->ignore($user->id)];
        }

        $data = $request->validate($rules);

        $user->name = $data['name'];

        // email / mobile are ignored for everyone except an Admin, even if the form was tampered with
        if ($this->canEditContact($request)) {
            $user->email = $data['email'];

            $mobile = preg_replace('/[ \-]/', '', (string) ($data['mobile'] ?? '')) ?: null;
            if ($mobile !== $user->mobile) {
                $user->mobile = $mobile;
                $user->mobile_verified_at = null;   // a new number has not been verified
            }
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = 'u' . $user->id . '-' . \Illuminate\Support\Str::random(12) . '.' . $file->extension();
            $file->storeAs('users', $name, 'public');

            if ($user->image && !str_contains($user->image, '/')) {
                Storage::disk('public')->delete('users/' . $user->image);
            }
            $user->image = $name;
        } elseif ($request->boolean('remove_photo') && $user->image) {
            if (!str_contains($user->image, '/')) {
                Storage::disk('public')->delete('users/' . $user->image);
            }
            $user->image = null;
        }

        $user->save();

        return redirect('admin/profile')->with('message', 'Profile updated');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'password.different' => 'The new password must be different from the current one.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is not correct.']);
        }

        $user->password = Hash::make($request->password);
        $user->setRememberToken(\Illuminate\Support\Str::random(60));
        $user->save();

        return redirect('admin/profile')->with('message', 'Password changed');
    }

    private function canEditContact(Request $request): bool
    {
        return $request->user()->type === 'Admin';
    }
}
