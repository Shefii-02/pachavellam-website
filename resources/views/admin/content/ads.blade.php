@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <h3 class="mb-1"><i class="bi bi-badge-ad me-1"></i>Google ads</h3>
    <div class="small text-muted mb-3">Apps use <b>AdMob</b>, the website uses <b>AdSense</b>. Nothing shows until the master switch, the platform switch and the ad type are all on. Ad units start with Google's test ids - replace them with your own before going live. The AdMob <i>app id</i> is part of the app build.</div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ url('admin/ads') }}">@csrf
        <div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
            <div class="d-flex flex-wrap gap-4 mb-3">
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="master" value="1" id="adM" {{ $master ? 'checked' : '' }}><label class="form-check-label fw-bold" for="adM">All ads</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="app_on" value="1" id="adA" {{ $appOn ? 'checked' : '' }}><label class="form-check-label" for="adA">Apps (AdMob)</label></div>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="web_on" value="1" id="adW" {{ $webOn ? 'checked' : '' }}><label class="form-check-label" for="adW">Website (AdSense)</label></div>
            </div>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">No full-screen ads in the first … minutes of a visit</label><input type="number" min="0" max="60" class="form-control" name="quiet" value="{{ $quiet }}"></div>
                <div class="col-md-4"><label class="form-label">At most … full-screen ads per visit</label><input type="number" min="0" max="20" class="form-control" name="session_max" value="{{ $sessionMax }}"></div>
                <div class="col-12 form-text">Calm rules: full-screen ads (interstitial, app-open) never appear on sign in, payment, tests or while a form is being filled, and the app-open ad only after a long break. Banners and in-list ads sit on reading pages only.</div>
            </div>
            <div class="mt-3">
                <label class="form-label fw-bold">Pages without ads (website)</label>
                <textarea class="form-control font-monospace" name="block_urls" rows="5" placeholder="tests/*&#10;kpsc/mcq/*&#10;kpsc/model-exam/*&#10;sign-in">{{ old('block_urls', $blockUrls) }}</textarea>
                <div class="form-text">One address per line, without the domain; <b>*</b> matches anything (<code>kpsc/psc-bulletin/*</code>). On these pages the AdSense script is not loaded at all, so <b>manual ads, the bottom banner and Google auto ads</b> are all off. Apps use the page tick-boxes of each ad below.</div>
            </div>
            <div class="col-md-5 mt-3"><label class="form-label">AdSense publisher id</label><input class="form-control" name="adsense_client" value="{{ old('adsense_client', $client) }}" placeholder="ca-pub-1234567890123456"></div>
        </div></div>

        @foreach ([['Apps (AdMob)', $app, 'app'], ['Website (AdSense)', $web, 'web']] as [$title, $units, $plat])
            <h5 class="mt-3">{{ $title }}</h5>
            @foreach ($units as $u)
                <div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="u[{{ $u->id }}][enabled]" value="1" id="en{{ $u->id }}" {{ $u->enabled ? 'checked' : '' }}><label class="form-check-label fw-bold" for="en{{ $u->id }}">{{ $u->label }}</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="u[{{ $u->id }}][hide_for_paid]" value="1" id="hp{{ $u->id }}" {{ $u->hide_for_paid ? 'checked' : '' }}><label class="form-check-label small" for="hp{{ $u->id }}">Hide for students who bought a course</label></div>
                    </div>
                    <div class="row g-2">
                        @if ($plat === 'app')
                            <div class="col-md-6"><label class="form-label small">Android unit id</label><input class="form-control form-control-sm" name="u[{{ $u->id }}][unit_android]" value="{{ $u->unit_android }}"></div>
                            <div class="col-md-6"><label class="form-label small">iOS unit id</label><input class="form-control form-control-sm" name="u[{{ $u->id }}][unit_ios]" value="{{ $u->unit_ios }}"></div>
                        @else
                            <div class="col-md-4"><label class="form-label small">AdSense ad slot id</label><input class="form-control form-control-sm" name="u[{{ $u->id }}][slot]" value="{{ $u->slot }}" placeholder="1234567890"></div>
                        @endif
                        @if (!in_array($u->type, ['interstitial', 'rewarded', 'app_open']))
                            <div class="col-12"><label class="form-label small mb-1">Shows on</label>
                                <div class="d-flex flex-wrap gap-3">@foreach ($pages as $k => $label)<div class="form-check"><input class="form-check-input" type="checkbox" name="u[{{ $u->id }}][pages][]" value="{{ $k }}" id="p{{ $u->id }}{{ $k }}" {{ in_array($k, (array) $u->pages) ? 'checked' : '' }}><label class="form-check-label small" for="p{{ $u->id }}{{ $k }}">{{ $label }}</label></div>@endforeach</div></div>
                        @else
                            <div class="col-12"><label class="form-label small mb-1">Shows when the student opens</label>
                                <div class="d-flex flex-wrap gap-3">@foreach ($pages as $k => $label)<div class="form-check"><input class="form-check-input" type="checkbox" name="u[{{ $u->id }}][pages][]" value="{{ $k }}" id="p{{ $u->id }}{{ $k }}" {{ in_array($k, (array) $u->pages) ? 'checked' : '' }}><label class="form-check-label small" for="p{{ $u->id }}{{ $k }}">{{ $label }}</label></div>@endforeach</div></div>
                            <div class="col-md-3"><label class="form-label small">At least this many minutes between two</label><input type="number" min="0" class="form-control form-control-sm" name="u[{{ $u->id }}][cap_minutes]" value="{{ $u->cap_minutes }}"></div>
                        @endif
                        @if (in_array($u->type, ['native', 'web_infeed']))
                            <div class="col-md-3"><label class="form-label small">One ad after every … items</label><input type="number" min="0" class="form-control form-control-sm" name="u[{{ $u->id }}][every_n]" value="{{ $u->every_n }}" placeholder="4 or more"></div>
                            <div class="col-md-3"><label class="form-label small">At most … ads per page of results</label><input type="number" min="1" class="form-control form-control-sm" name="u[{{ $u->id }}][max_per_page]" value="{{ $u->max_per_page }}"></div>
                            <div class="col-md-3"><label class="form-label small">Start from page</label><input type="number" min="1" class="form-control form-control-sm" name="u[{{ $u->id }}][start_page]" value="{{ $u->start_page }}"></div>
                            <div class="col-12 form-text">In-list ads only appear on list pages: {{ implode(', ', array_map(fn ($k) => $pages[$k], $lists)) }}.</div>
                        @endif
                    </div>
                </div></div>
            @endforeach
        @endforeach
        <button class="btn btn-success mb-0">Save ad settings</button>
    </form>
</div>
@endsection
