@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title)
@section('content')
<div class="page-content-wrapper"><div class="container py-4" style="max-width:640px">
    <h3 class="fw-bold">Too many devices</h3>
    <p class="text-muted">Hi {{ $name }}, your account can be signed in on {{ $limit }} device{{ $limit == 1 ? '' : 's' }} at a time and it is already full. Sign out of one to continue here.</p>
    @if (session('error'))<div class="alert alert-warning py-2">{{ session('error') }}</div>@endif
    <form method="POST" action="{{ url('devices/limit/release') }}">@csrf
        @include('kpsc.partials.device-list', ['rows' => $rows, 'selectable' => true])
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button class="btn btn-success rounded-pill px-4">Sign out selected and continue</button>
            <button class="btn btn-outline-dark rounded-pill" name="all" value="1" onclick="return confirm('Sign out of every other device and continue here?')">Sign out all other devices</button>
            <a class="btn btn-link" href="{{ url('sign-in') }}">Cancel</a>
        </div>
    </form>
</div></div>
@endsection
