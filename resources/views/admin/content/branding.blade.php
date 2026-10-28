@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <h3 class="mb-1"><i class="bi bi-brush me-1"></i>Logos</h3>
    <div class="small text-muted mb-3">Upload one logo for <b>light mode</b> and one for <b>dark mode</b>. The website switches between them with the theme. If only one is uploaded it is used for both.</div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
    <form method="POST" action="{{ url('admin/branding') }}" enctype="multipart/form-data">@csrf
        <div class="row g-3">
            @foreach ([['light', 'Light mode logo', $light, $lightUrl, '#ffffff'], ['dark', 'Dark mode logo', $dark, $darkUrl, '#0f172a']] as [$mode, $label, $own, $shown, $bg])
                <div class="col-md-6"><div class="border rounded-3 p-3">
                    <label class="form-label fw-bold">{{ $label }}</label>
                    <div class="rounded-3 d-flex align-items-center justify-content-center mb-2" style="background:{{ $bg }};height:110px;border:1px solid #dee2e6"><img src="{{ $shown }}" alt="" style="max-height:80px;max-width:90%"></div>
                    <input class="form-control" type="file" name="logo_{{ $mode }}" accept="image/*">@error('logo_' . $mode)<div class="text-danger small">{{ $message }}</div>@enderror
                    @if ($own)<div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_{{ $mode }}" value="1" id="rm{{ $mode }}"><label class="form-check-label small" for="rm{{ $mode }}">Remove the uploaded logo</label></div>@endif
                </div></div>
            @endforeach
        </div>
        <div class="mt-3"><button class="btn btn-success mb-0">Save logos</button></div>
    </form>
</div>
@endsection
