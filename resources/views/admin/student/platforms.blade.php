@extends('admin.student.section')
@section('content2')
@php
    $exportUrl = student_cms('platforms/export') . (request()->getQueryString() ? '?' . request()->getQueryString() : '');
    $icons = ['ios' => 'bi-apple', 'android' => 'bi-android2', 'web' => 'bi-globe', 'app' => 'bi-phone'];
    $colors = ['ios' => 'dark', 'android' => 'success', 'web' => 'primary', 'app' => 'secondary'];
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">Platforms &amp; App Versions</h3>
        <div class="small text-muted">
            iOS, Android and web users with their last active time, app version and device.
            @if ($trackingSince) Tracking since {{ \Carbon\Carbon::parse($trackingSince)->format('d M Y, h:i A') }}.@endif
        </div>
    </div>
    <a href="{{ $exportUrl }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-download me-1"></i>Export CSV ({{ number_format($list->total()) }})</a>
</div>

@if (!$people || !$people->total)
    <div class="alert alert-info small">
        No platform data has been recorded yet. It is collected from now on, as users open the app or the website
        (the app needs the updated build that sends its platform and version).
    </div>
@endif

{{-- per platform cards --}}
<div class="row g-3 mb-4">
    @foreach (['ios', 'android', 'web', 'app'] as $p)
        @php $s = $summary[$p] ?? null; @endphp
        <div class="col-6 col-xl-3">
            <a href="{{ student_cms('platforms') }}?platform={{ $p }}&type={{ $f['type'] }}" class="text-reset">
                <div class="card bg-transparent border rounded-3 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-{{ $colors[$p] }} me-2"><i class="bi {{ $icons[$p] }}"></i></span>
                            <strong>{{ \App\Services\ClientInfo::label($p) }}</strong>
                        </div>
                        <div class="h3 mb-1">{{ $s ? number_format($s->users) : 0 }} <small class="text-muted fs-6">users</small></div>
                        <div class="small text-muted">
                            24h <strong class="text-body">{{ $s ? number_format($s->d1) : 0 }}</strong> ·
                            7d <strong class="text-body">{{ $s ? number_format($s->d7) : 0 }}</strong> ·
                            30d <strong class="text-body">{{ $s ? number_format($s->d30) : 0 }}</strong>
                        </div>
                        <div class="small text-muted mt-1">Last seen {{ $s && $s->last_seen ? \Carbon\Carbon::parse($s->last_seen)->diffForHumans() : '—' }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>
<p class="small text-muted">
    @if ($people && $people->total)
        {{ number_format($people->total) }} different people seen so far
        ({{ number_format($people->d1) }} in 24h · {{ number_format($people->d7) }} in 7 days · {{ number_format($people->d30) }} in 30 days) out of {{ number_format($totalUsers) }}
        {{ $f['type'] === 'all' ? 'users' : strtolower($f['type']) . 's' }}.
        Someone using both the app and the website is counted on each platform but once here.
    @endif
    “App (older build)” = app users whose build does not send the platform yet; they move to iOS/Android after updating.
</p>

{{-- versions --}}
<div class="row g-3 mb-4">
    @forelse ($versions as $platform => $v)
        <div class="col-lg-4">
            <div class="card bg-transparent border rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
                    <h5 class="mb-0"><i class="bi {{ $icons[$platform] }} me-1"></i>{{ \App\Services\ClientInfo::label($platform) }} versions</h5>
                    <span class="small text-muted">{{ $v['latest'] ? 'newest seen ' . $v['latest'] : '' }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach ($v['rows'] as $r)
                                <tr>
                                    <td>
                                        <a href="{{ student_cms('platforms') }}?platform={{ $platform }}&version={{ $r->version ?? 'unknown' }}&type={{ $f['type'] }}">{{ $r->version ?? 'unknown' }}</a>
                                        @if ($r->outdated)<span class="badge bg-warning text-dark">outdated</span>@endif
                                    </td>
                                    <td style="width:35%">
                                        <div class="progress" style="height:6px"><div class="progress-bar bg-{{ $colors[$platform] }}" style="width:{{ $r->pct }}%"></div></div>
                                    </td>
                                    <td class="text-end text-nowrap">{{ number_format($r->users) }} <small class="text-muted">({{ $r->pct }}%)</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="text-muted small">No mobile app versions recorded yet.</div></div>
    @endforelse
    @if ($browsers->isNotEmpty())
        <div class="col-lg-4">
            <div class="card bg-transparent border rounded-3 h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="bi bi-globe me-1"></i>Web browsers</h5></div>
                <div class="card-body p-0">
                    <table class="table table-sm align-middle mb-0">
                        <tbody>
                            @foreach ($browsers as $b)
                                <tr><td>{{ $b->device_model }}</td><td class="text-end">{{ number_format($b->users) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- users --}}
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <form method="GET" class="row g-2">
            <div class="col-lg-3"><input type="text" name="q" value="{{ $f['q'] }}" class="form-control form-control-sm" placeholder="Name, email or mobile..."></div>
            <div class="col-6 col-lg-2">
                <select name="platform" class="form-select form-select-sm">
                    <option value="">All platforms</option>
                    @foreach (\App\Services\ClientInfo::PLATFORM_LABELS as $k => $label)
                        <option value="{{ $k }}" @selected($f['platform'] === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="version" class="form-select form-select-sm">
                    <option value="">Any version</option>
                    <option value="unknown" @selected($f['version'] === 'unknown')>Version unknown</option>
                    @foreach ($versionOptions as $ver)
                        <option value="{{ $ver }}" @selected($f['version'] === $ver)>{{ $ver }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="window" class="form-select form-select-sm">
                    <option value="">Any activity</option>
                    @foreach ($windows as $k => $label)
                        <option value="{{ $k }}" @selected($f['window'] === $k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <select name="type" class="form-select form-select-sm">
                    <option value="all" @selected($f['type'] === 'all')>All user types</option>
                    @foreach ($types as $t)
                        <option value="{{ $t }}" @selected($f['type'] === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto d-flex gap-2">
                <button class="btn btn-sm btn-primary mb-0">Filter</button>
                <a href="{{ student_cms('platforms') }}" class="btn btn-sm btn-light mb-0">Reset</a>
            </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                <thead>
                    <tr>
                        <th class="border-0">User</th>
                        <th class="border-0">Platform</th>
                        <th class="border-0">App version</th>
                        <th class="border-0">Device</th>
                        <th class="border-0">Last active</th>
                        <th class="border-0">First seen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list as $r)
                        @php $active = $r->last_active_at && \Carbon\Carbon::parse($r->last_active_at)->gt(now()->subDay()); @endphp
                        <tr>
                            <td>
                                <a href="{{ student_cms('student-list/' . $r->user_id) }}">{{ $r->name ?: '(no name)' }}</a>
                                <div class="small text-muted">{{ $r->mobile ?: $r->email }}</div>
                            </td>
                            <td><span class="badge bg-{{ $colors[$r->platform] ?? 'secondary' }}"><i class="bi {{ $icons[$r->platform] ?? 'bi-phone' }} me-1"></i>{{ \App\Services\ClientInfo::label($r->platform) }}</span></td>
                            <td>{{ $r->app_version ? $r->app_version . ($r->app_build ? ' (' . $r->app_build . ')' : '') : '—' }}</td>
                            <td class="small">{{ $r->device_model ?: '—' }}@if ($r->os_version)<div class="text-muted">{{ $r->platform === 'ios' ? 'iOS' : ($r->platform === 'android' ? 'Android' : '') }} {{ $r->os_version }}</div>@endif</td>
                            <td class="text-nowrap">
                                @if ($active)<span class="badge bg-success me-1">online</span>@endif
                                {{ \Carbon\Carbon::parse($r->last_active_at)->diffForHumans() }}
                                <div class="small text-muted">{{ \Carbon\Carbon::parse($r->last_active_at)->format('d M Y, h:i A') }}</div>
                            </td>
                            <td class="small text-nowrap">{{ $r->first_seen_at ? \Carbon\Carbon::parse($r->first_seen_at)->format('d M Y') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No users match.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links('pagination::bootstrap-5') }}</div>
        <p class="small text-muted mb-0 mt-2">One row per user and platform. “Last active” is the last successful request (app or website); it is refreshed at most every 5 minutes.</p>
    </div>
</div>
@endsection
