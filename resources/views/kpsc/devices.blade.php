@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-4" style="max-width:640px">
    <h3 class="fw-bold">My devices</h3>
    <p class="text-muted">{{ $limit > 0 ? 'You can be signed in on ' . $limit . ' device' . ($limit == 1 ? '' : 's') . ' at a time (' . $rows->count() . ' now).' : 'Where you are signed in.' }} Signing a device out takes effect at once.</p>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
    <form method="POST">@csrf
        @include('kpsc.partials.device-list', ['rows' => $rows, 'sid' => $sid, 'removeUrl' => fn ($id) => url('devices/' . $id . '/remove')])
        @if ($rows->count() > 1)<button class="btn btn-outline-dark rounded-pill mt-2" formaction="{{ url('devices/logout-others') }}" formmethod="POST" onclick="return confirm('Sign out of every other device?')">Sign out all other devices</button>@endif
    </form>
</div></div>
@endsection
