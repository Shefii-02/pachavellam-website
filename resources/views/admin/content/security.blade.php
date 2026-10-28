@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <h3 class="mb-1"><i class="bi bi-shield-lock me-1"></i>Security</h3>
    <div class="small text-muted mb-3">Our own CAPTCHA, request limits against floods (DoS) and IP block lists. Changes apply within a minute.</div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

    <div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
        <h5>CAPTCHA</h5>
        <form method="POST" action="{{ url('admin/security/captcha') }}">@csrf
            <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="on" value="1" id="cpOn" {{ $on ? 'checked' : '' }}><label class="form-check-label fw-bold" for="cpOn">CAPTCHA is {{ $on ? 'ON' : 'OFF' }}</label></div>
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label">Guard these forms</label>
                    @foreach ($forms as $k => $label)<div class="form-check"><input class="form-check-input" type="checkbox" name="forms[]" value="{{ $k }}" id="cf{{ $k }}" {{ in_array($k, $chosen) ? 'checked' : '' }}><label class="form-check-label" for="cf{{ $k }}">{{ $label }}</label></div>@endforeach</div>
                <div class="col-md-4"><label class="form-label">Ask</label>
                    <select name="mode" class="form-select"><option value="always" {{ $mode === 'always' ? 'selected' : '' }}>Every time</option><option value="after_failures" {{ $mode === 'after_failures' ? 'selected' : '' }}>Only after repeated failures from one IP</option></select></div>
                <div class="col-md-3"><label class="form-label">Failures before asking</label><input type="number" min="1" max="20" class="form-control" name="failures" value="{{ $failures }}"></div>
            </div>
            <div class="mt-3"><button class="btn btn-success mb-0">Save CAPTCHA settings</button></div>
        </form>
    </div></div>

    <div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
        <h5>Request limits (per IP, per minute)</h5>
        <form method="POST" action="{{ url('admin/security/limits') }}">@csrf
            <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" name="on" value="1" id="rlOn" {{ $rl['on'] === '1' ? 'checked' : '' }}><label class="form-check-label fw-bold" for="rlOn">Limits are {{ $rl['on'] === '1' ? 'ON' : 'OFF' }}</label></div>
            <div class="row g-3">
                <div class="col-md-3"><label class="form-label">Website pages</label><input type="number" min="0" class="form-control" name="web" value="{{ $rl['web'] }}"></div>
                <div class="col-md-3"><label class="form-label">App / API calls</label><input type="number" min="0" class="form-control" name="api" value="{{ $rl['api'] }}"></div>
                <div class="col-md-3"><label class="form-label">Sign in, OTP, forms</label><input type="number" min="0" class="form-control" name="auth" value="{{ $rl['auth'] }}"></div>
                <div class="col-md-3"></div>
                <div class="col-md-3"><label class="form-label">Block an IP after this many limit hits (0 = never)</label><input type="number" min="0" class="form-control" name="after" value="{{ $rl['after'] }}"></div>
                <div class="col-md-3"><label class="form-label">…for how many minutes</label><input type="number" min="1" class="form-control" name="minutes" value="{{ $rl['minutes'] }}"></div>
            </div>
            <div class="form-text mt-2">0 means no limit for that kind. The admin panel and payment callbacks are never counted. Schools and offices share one IP, so keep the numbers generous.</div>
            <div class="mt-3"><button class="btn btn-success mb-0">Save limits</button></div>
        </form>
    </div></div>

    <h5>Blocked and always-allowed IPs</h5>
    <form method="POST" action="{{ url('admin/security/ips') }}" class="row g-2 mb-3">@csrf
        <div class="col-md-3"><input class="form-control" name="ip" placeholder="IP address" required></div>
        <div class="col-md-2"><select name="kind" class="form-select"><option value="block">Block</option><option value="allow">Always allow</option></select></div>
        <div class="col-md-3"><input class="form-control" name="reason" placeholder="Reason (optional)" maxlength="150"></div>
        <div class="col-md-2"><input type="number" min="1" class="form-control" name="hours" placeholder="Hours (blank = for good)"></div>
        <div class="col-md-2"><button class="btn btn-light mb-0 w-100">Add</button></div>
    </form>
    <div class="small text-muted mb-2">Your IP now: <b>{{ $myIp }}</b></div>
    <div class="table-responsive"><table class="table table-dark-gray align-middle mb-0">
        <thead><tr><th>IP</th><th>List</th><th>Reason</th><th>Until</th><th></th></tr></thead>
        <tbody>
            @forelse ($ips as $i)
                <tr><td class="fw-bold">{{ $i->ip }}</td><td><span class="badge {{ $i->kind === 'block' ? 'bg-danger' : 'bg-success' }}">{{ $i->kind === 'block' ? 'Blocked' : 'Allowed' }}</span></td><td class="small">{{ $i->reason }}</td><td class="small">{{ $i->until ? $i->until->format('d M Y H:i') . ($i->until->isPast() ? ' (over)' : '') : 'until removed' }}</td>
                    <td class="text-end"><form method="POST" action="{{ url('admin/security/ips/' . $i->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft mb-0"><i class="bi bi-trash"></i></button></form></td></tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No IPs listed.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection
