@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <h3 class="mb-1"><i class="bi bi-phone me-1"></i>Devices</h3>
    <div class="small text-muted mb-3">Everyone who is signed in, on the website or in an app. A removed device stops working on its very next request. Staff are never limited.</div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif

    <div class="row g-2 mb-3">
        @foreach ([['Active devices', $stats['devices']], ['People', $stats['people']], ['Website', $stats['web']], ['Apps', $stats['app']], ['Online now (10 min)', $stats['online']], ['Removed (24 h)', $stats['blocked']]] as [$l, $n])
            <div class="col-6 col-md-2"><div class="border rounded p-2 text-center"><div class="h4 mb-0">{{ $n }}</div><div class="small text-muted">{{ $l }}</div></div></div>
        @endforeach
    </div>
    <div class="d-flex flex-wrap gap-2 mb-3">@foreach ($byPlatform as $p)<span class="badge bg-light text-dark border">{{ $p->channel === 'web' ? 'Website' : ucfirst($p->platform ?: 'app') . ' app' }}: {{ $p->devices }} devices / {{ $p->people }} people</span>@endforeach</div>

    <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
        <form method="POST" action="{{ url('admin/devices/settings') }}" class="row g-2 align-items-end">@csrf
            <div class="col-md-3"><label class="form-label">Devices per person (0 = no limit)</label><input type="number" min="0" max="20" class="form-control" name="device_limit" value="{{ $limit }}"></div>
            <div class="col-md-4"><label class="form-label">A device unused for this many days stops counting</label><input type="number" min="1" max="365" class="form-control" name="device_stale_days" value="{{ $stale }}"></div>
            <div class="col-md-2"><button class="btn btn-success mb-0 w-100">Save</button></div>
        </form>
    </div></div>

    @if ($user)
        <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div><b>{{ $user->name }}</b> <span class="text-muted">{{ $user->email ?: $user->mobile }}</span> - limit {{ $userLimit ?: 'none' }}</div>
            <div class="d-flex gap-2 align-items-center">
                <form method="POST" action="{{ url('admin/devices/user/' . $user->id . '/limit') }}" class="d-flex gap-1">@csrf<input type="number" min="0" max="20" name="device_limit" class="form-control form-control-sm" style="width:90px" placeholder="common" value="{{ $user->device_limit }}"><button class="btn btn-sm btn-light mb-0">Set limit</button></form>
                <form method="POST" action="{{ url('admin/devices/user/' . $user->id . '/remove-all') }}" onsubmit="return confirm('Sign this person out everywhere?')">@csrf<button class="btn btn-sm btn-danger mb-0">Sign out everywhere</button></form>
                <a href="{{ url('admin/devices') }}" class="btn btn-sm btn-light mb-0">All people</a>
            </div>
        </div>
    @else
        <form class="row g-2 mb-3" method="GET"><div class="col-md-4"><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Search name, email or mobile"></div>
            <div class="col-md-2"><select name="channel" class="form-select"><option value="">Website + apps</option><option value="web" {{ request('channel') === 'web' ? 'selected' : '' }}>Website</option><option value="app" {{ request('channel') === 'app' ? 'selected' : '' }}>Apps</option></select></div>
            <div class="col-md-2"><button class="btn btn-light mb-0">Filter</button></div></form>
    @endif

    <div class="table-responsive"><table class="table table-dark-gray align-middle mb-0">
        <thead><tr><th>Person</th><th>Device</th><th>Signed in</th><th>Last used</th><th>IP</th><th class="text-end"></th></tr></thead>
        <tbody>
            @forelse ($rows as $d)
                <tr>
                    <td><a href="{{ url('admin/devices?user=' . $d->user_id) }}" class="fw-bold">{{ $d->user->name ?? 'User #' . $d->user_id }}</a><div class="small text-muted">{{ $d->user->email ?? $d->user->mobile ?? '' }}</div></td>
                    <td><i class="bi {{ $d->channel === 'app' ? 'bi-phone' : 'bi-laptop' }} me-1"></i>{{ $d->device_name ?: ($d->channel === 'app' ? 'Mobile app' : 'Web browser') }}<div class="small text-muted">{{ $d->channel === 'app' ? ucfirst($d->platform ?: 'app') . ' ' . $d->app_version : 'Website' }}</div></td>
                    <td class="small">{{ $d->created_at->format('d M Y H:i') }}</td>
                    <td class="small">{{ $d->last_active_at ? $d->last_active_at->diffForHumans() : '-' }}</td>
                    <td class="small">{{ $d->ip }}</td>
                    <td class="text-end"><form method="POST" action="{{ url('admin/devices/' . $d->id . '/remove') }}" onsubmit="return confirm('Sign this device out?')">@csrf<button class="btn btn-sm btn-danger-soft mb-0"><i class="bi bi-x-circle me-1"></i>Remove</button></form></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">No signed in devices.</td></tr>
            @endforelse
        </tbody>
    </table></div>
    <div class="mt-2">{{ $rows->links() }}</div>
</div>
@endsection
