@extends('admin.student.section')
@section('content2')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><i class="bi bi-gear me-1"></i>Refer &amp; earn - coins and rules</h3>
    <a href="{{ student_cms('referrals') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Referrals</a>
</div>
@if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
<div class="card bg-transparent border rounded-3" style="max-width:720px"><div class="card-body">
    <form method="POST" action="{{ student_cms('referrals/settings') }}">@csrf
        <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" role="switch" name="enabled" id="en" value="1" {{ old('enabled', $s['enabled']) ? 'checked' : '' }}><label class="form-check-label fw-semibold" for="en">Referral programme is on</label>
            <div class="form-text">Off: the links and codes stop counting new joins. Coins already given stay.</div></div>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Coins for the student who shares <span class="text-danger">*</span></label><input type="number" min="0" class="form-control" name="referrer_coins" value="{{ old('referrer_coins', $s['referrer_coins']) }}" required>@error('referrer_coins')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label">Coins for the friend who joins <span class="text-danger">*</span></label><input type="number" min="0" class="form-control" name="referred_coins" value="{{ old('referred_coins', $s['referred_coins']) }}" required>@error('referred_coins')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label">Give the coins</label>
                <select name="qualify" class="form-select"><option value="first_test" {{ old('qualify', $s['qualify']) === 'first_test' ? 'selected' : '' }}>After the friend finishes a first test (recommended)</option><option value="signup" {{ old('qualify', $s['qualify']) === 'signup' ? 'selected' : '' }}>As soon as the friend joins</option></select>
                <div class="form-text">"First test" stops fake accounts: coins are paid only for students who really use the app.</div></div>
            <div class="col-md-6"><label class="form-label">Most friends one student can bring</label><input type="number" min="0" class="form-control" name="max_per_user" value="{{ old('max_per_user', $s['max_per_user']) }}"><div class="form-text">0 = no limit.</div></div>
        </div>
        <div class="alert alert-info small mt-3 mb-0"><b>How it reaches students</b><br>
            Each student gets a code and the link <code>{{ url('r/CODE') }}</code> under My account &rarr; Refer &amp; earn. The link opens a page that sends Android users to Play Store (the code goes with the install), iPhone users to the App Store, and everyone else to the website sign in. The apps send the code as <code>ref_code</code> (or the header <code>X-Referral-Code</code>) when the student signs up, or call <code>POST /api/auth/referral/apply</code> once they are signed in.</div>
        <div class="mt-3"><button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button></div>
    </form>
</div></div>
@endsection
