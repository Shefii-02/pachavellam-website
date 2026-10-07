<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\OtpLog;
use App\Models\User;
use Illuminate\Http\Request;

class SignInLogController extends Controller
{
    public function otpLogs(Request $request)
    {
        $logs = OtpLog::query()
            ->when($request->filled('q'), fn ($q) => $q->where('phone', 'like', '%' . preg_replace('/\D/', '', $request->q) . '%'))
            ->when($request->filled('status'), function ($q) use ($request) {
                match ($request->status) {
                    'expired' => $q->where('status', 'sent')->where('expires_at', '<', now()),
                    'sent' => $q->where('status', 'sent')->where('expires_at', '>=', now()),
                    default => $q->where('status', $request->status),
                };
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.general.otp-logs', compact('logs'));
    }

    public function otpLogDelete($id)
    {
        OtpLog::where('id', $id)->delete();
        return redirect()->back()->with('message', 'OTP log deleted successfully');
    }

    public function googleSignIns(Request $request)
    {
        return $this->providerList($request, 'google_id', 'Google Sign In List', 'Google ID');
    }

    public function appleSignIns(Request $request)
    {
        return $this->providerList($request, 'apple_id', 'Apple Sign In List', 'Apple ID');
    }

    private function providerList(Request $request, string $column, string $title, string $idLabel)
    {
        $users = User::whereNotNull($column)->where($column, '!=', '')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%' . $request->q . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('email', 'like', $term)->orWhere('mobile', 'like', $term));
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.general.provider-signins', compact('users', 'column', 'title', 'idLabel'));
    }
}
