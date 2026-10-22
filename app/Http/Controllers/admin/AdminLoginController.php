<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminArea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if ($user && in_array($user->type, EnsureAdminArea::ALLOWED_TYPES, true)) {
            return redirect('/admin');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        // One generic message for wrong password, unknown email and non-admin accounts,
        // so the form doesn't reveal which emails belong to students.
        if (
            !$user
            || !in_array($user->type, EnsureAdminArea::ALLOWED_TYPES, true)
            || !Hash::check($data['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match an admin account.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $request)
    {
        // viewing the panel as a staff member: signing out returns the super admin to the admin dashboard
        if ($back = \App\Http\Controllers\Staff\ImpersonationController::returnToAdmin($request)) {
            return $back;
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
