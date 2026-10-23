@extends('admin.student.section')
@section('content2')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div><h3 class="mb-0"><i class="bi bi-gift me-1"></i>Refer &amp; earn</h3>
        <div class="small text-muted">{{ $settings['enabled'] ? 'Running' : 'Switched off' }} · referrer gets <b>{{ $settings['referrer_coins'] }}</b> coins, friend gets <b>{{ $settings['referred_coins'] }}</b> · paid {{ $settings['qualify'] === 'signup' ? 'when the friend joins' : 'after the friend finishes a first test' }}</div></div>
    <a href="{{ student_cms('referrals/settings') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-gear me-1"></i>Coins &amp; settings</a>
</div>
@if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif

<div class="row g-3 mb-3">
    @foreach ([['Joined through a link', $tot->c ?? 0, 'bi-people'], ['Rewarded', $tot->rewarded ?? 0, 'bi-check-circle'], ['Waiting for a first test', $tot->pending ?? 0, 'bi-hourglass-split'], ['Coins given', $tot->coins ?? 0, 'bi-coin']] as [$l, $n, $ic])
        <div class="col-6 col-lg-3"><div class="card bg-transparent border rounded-3 h-100"><div class="card-body py-3"><div class="text-muted small"><i class="bi {{ $ic }} me-1"></i>{{ $l }}</div><div class="fs-3 fw-bold">{{ number_format((int) $n) }}</div></div></div></div>
    @endforeach
</div>
<div class="row g-3 mb-3">
    <div class="col-lg-5"><div class="card bg-transparent border rounded-3 h-100"><div class="card-header bg-transparent"><b>By device</b></div><div class="card-body">
        @php $pt = max(1, $byPlatform->sum()); @endphp
        @foreach (['web' => ['Website', 'bi-globe2', 'primary'], 'android' => ['Android', 'bi-android2', 'success'], 'ios' => ['iPhone', 'bi-apple', 'dark']] as $k => [$lbl, $ic, $col])
            <div class="mb-2"><div class="d-flex justify-content-between small"><span><i class="bi {{ $ic }} me-1"></i>{{ $lbl }}</span><span>{{ (int) ($byPlatform[$k] ?? 0) }}</span></div><div class="progress" style="height:7px"><div class="progress-bar bg-{{ $col }}" style="width:{{ round(($byPlatform[$k] ?? 0) / $pt * 100) }}%"></div></div></div>
        @endforeach
    </div></div></div>
    <div class="col-lg-7"><div class="card bg-transparent border rounded-3 h-100"><div class="card-header bg-transparent"><b>Best referrers</b></div>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Student</th><th class="text-center">Joined</th><th class="text-center">Rewarded</th><th class="text-end">Coins</th></tr></thead><tbody>
            @forelse ($top as $t)<tr><td><a href="{{ student_cms('student-list/' . $t->id) }}">{{ $t->name ?: '#' . $t->id }}</a><div class="small text-muted">{{ $t->mobile }}</div></td><td class="text-center">{{ $t->joined }}</td><td class="text-center">{{ (int) $t->rewarded }}</td><td class="text-end">{{ number_format((int) $t->coins) }}</td></tr>@empty<tr><td colspan="4" class="text-center text-muted py-3">No referrals yet.</td></tr>@endforelse
        </tbody></table></div></div></div>
</div>

<form class="row g-2 mb-3" method="GET">
    <div class="col-md-3"><input class="form-control form-control-sm" name="q" value="{{ request('q') }}" placeholder="Name, mobile or code"></div>
    <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">Any status</option>@foreach (['joined' => 'Waiting', 'rewarded' => 'Rewarded', 'rejected' => 'Rejected'] as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-2"><select name="platform" class="form-select form-select-sm"><option value="">Any device</option>@foreach (['web' => 'Website', 'android' => 'Android', 'ios' => 'iPhone'] as $k => $l)<option value="{{ $k }}" {{ request('platform') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
    <div class="col-md-2"><input type="date" class="form-control form-control-sm" name="from" value="{{ request('from') }}"></div>
    <div class="col-md-2"><input type="date" class="form-control form-control-sm" name="to" value="{{ request('to') }}"></div>
    <div class="col-md-1"><button class="btn btn-sm btn-primary w-100 mb-0">Filter</button></div>
</form>

<div class="card bg-transparent border rounded-3"><div class="table-responsive"><table class="table align-middle mb-0 table-hover">
    <thead><tr><th>Joined</th><th>Invited by</th><th>New student</th><th>Device</th><th>Status</th><th class="text-end">Coins (inviter / friend)</th><th></th></tr></thead>
    <tbody>
    @forelse ($list as $r)
        <tr>
            <td class="small">{{ \Illuminate\Support\Carbon::parse($r->created_at)->format('d M Y, h:i a') }}</td>
            <td><a href="{{ student_cms('student-list/' . $r->referrer_id) }}">{{ $r->referrer_name ?: '#' . $r->referrer_id }}</a><div class="small text-muted">{{ $r->referrer_mobile }} · {{ $r->code }}</div></td>
            <td><a href="{{ student_cms('student-list/' . $r->referred_id) }}">{{ $r->referred_name ?: '#' . $r->referred_id }}</a><div class="small text-muted">{{ $r->referred_mobile }}</div></td>
            <td><span class="badge bg-{{ ['web' => 'primary', 'android' => 'success', 'ios' => 'dark'][$r->platform] ?? 'secondary' }}">{{ ['web' => 'Website', 'android' => 'Android', 'ios' => 'iPhone'][$r->platform] ?? $r->platform }}</span></td>
            <td><span class="badge bg-{{ ['joined' => 'warning text-dark', 'rewarded' => 'success', 'rejected' => 'secondary'][$r->status] ?? 'secondary' }}">{{ ['joined' => 'Waiting', 'rewarded' => 'Rewarded', 'rejected' => 'Rejected'][$r->status] ?? $r->status }}</span></td>
            <td class="text-end">{{ $r->referrer_coins }} / {{ $r->referred_coins }}</td>
            <td class="text-end text-nowrap">
                @if ($r->status === 'joined')
                    <form class="d-inline" method="POST" action="{{ student_cms('referrals/' . $r->id . '/reward') }}">@csrf<button class="btn btn-sm btn-success mb-0" title="Give the coins now">Reward now</button></form>
                    <form class="d-inline" method="POST" action="{{ student_cms('referrals/' . $r->id . '/reject') }}" onsubmit="return confirm('Reject this referral? No coins will be given.')">@csrf<button class="btn btn-sm btn-outline-danger mb-0">Reject</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-5">No referrals found.</td></tr>
    @endforelse
    </tbody></table></div></div>
<div class="mt-3">@include('admin.partials.per-page', ['p' => $list])
{{ $list->links() }}</div>
@endsection
