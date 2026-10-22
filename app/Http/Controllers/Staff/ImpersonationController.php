<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * "Login as" for the super admin: open a staff member's own dashboard and chat exactly as they see them.
 * The admin's id is kept in the session; "Return to my account" - or signing out - brings the admin back to the admin dashboard.
 */
class ImpersonationController extends Controller
{
    public const KEY = 'impersonator_id';

    public function start(Request $request, $id)
    {
        abort_unless(StaffAccess::isAdmin($request->user()) && !$request->session()->has(self::KEY), 403, 'Only an Admin can open a staff account like this.');
        $staff = User::where('type', 'Admin Staff')->findOrFail($id);

        ActivityLogger::record('login', 'Impersonation', 'Opened the account of ' . $staff->name . ' (' . $staff->email . ')');
        $admin = $request->user();
        Auth::login($staff);
        $request->session()->regenerate();
        $request->session()->put(self::KEY, $admin->id);

        return redirect(url('admin'))->with('success', 'You are now viewing the panel as ' . $staff->name . '.');
    }

    public function stop(Request $request)
    {
        return self::returnToAdmin($request) ?? redirect(url('admin'));
    }

    /** Puts the admin back (used by "Return" and by signing out while viewing as staff). Null when not impersonating. */
    public static function returnToAdmin(Request $request)
    {
        $adminId = $request->session()->get(self::KEY);
        $admin = $adminId ? User::where('type', 'Admin')->find($adminId) : null;
        if (!$admin) {
            $request->session()->forget(self::KEY);

            return null;
        }
        $staffName = optional($request->user())->name;

        Auth::login($admin);
        $request->session()->regenerate();
        $request->session()->forget(self::KEY);
        ActivityLogger::record('logout', 'Impersonation', 'Closed the account of ' . $staffName . ' and returned to the Admin dashboard');

        return redirect(url('admin'))->with('success', 'Welcome back. You are on your own account again.');
    }
}
