{{-- A table-less list of devices. Needs $rows; optional $sid (this device), $selectable (checkboxes), $removeUrl (fn id => url) --}}
@foreach ($rows as $d)
    <label class="d-flex align-items-center gap-3 p-3 mb-2 bg-white rounded-4 shadow-sm" style="cursor:{{ ($selectable ?? false) ? 'pointer' : 'default' }}">
        @if ($selectable ?? false)<input type="checkbox" name="ids[]" value="{{ $d->id }}" class="form-check-input mt-0">@endif
        <i class="bi {{ $d->channel === 'app' ? 'bi-phone' : 'bi-laptop' }}" style="font-size:1.6rem;color:#0a8f5b"></i>
        <div class="flex-grow-1" style="min-width:0">
            <div class="fw-bold text-truncate">{{ $d->device_name ?: ($d->channel === 'app' ? 'Mobile app' : 'Web browser') }}@if (($sid ?? null) === $d->sid) <span class="badge bg-success ms-1">This device</span>@endif</div>
            <div class="small text-muted">{{ $d->channel === 'app' ? ucfirst($d->platform ?: 'app') . ' app' . ($d->app_version ? ' ' . $d->app_version : '') : 'Website' }} · signed in {{ $d->created_at->format('d M Y') }} · last used {{ $d->last_active_at ? $d->last_active_at->diffForHumans() : '-' }}@if ($d->ip) · {{ $d->ip }}@endif</div>
        </div>
        @isset($removeUrl)@if (($sid ?? null) !== $d->sid)<button class="btn btn-sm btn-outline-danger rounded-pill" formaction="{{ $removeUrl($d->id) }}" formmethod="POST">Sign out</button>@endif @endisset
    </label>
@endforeach
