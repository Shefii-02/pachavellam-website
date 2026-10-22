@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-chat-square-text me-1"></i>Video chats</h3>
            <div class="small text-muted">The live chat beside free classes and videos ({{ number_format($totalMessages) }} messages). Student and staff conversations are in <a href="{{ url('admin/chat') }}">Chats</a>.</div>
        </div>
        <form class="d-flex gap-2" method="GET">
            <input class="form-control form-control-sm" name="q" value="{{ $q }}" placeholder="Search video…">
            <select name="state" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">All</option><option value="open" {{ $state === 'open' ? 'selected' : '' }}>Open now</option><option value="busy" {{ $state === 'busy' ? 'selected' : '' }}>Has messages</option></select>
            <button class="btn btn-sm btn-primary mb-0">Search</button>
        </form>
    </div>

    <div class="table-responsive border rounded-3">
        <table class="table align-middle mb-0 table-hover">
            <thead><tr><th>Video</th><th>Exam type</th><th>Chat</th><th class="text-center">Messages</th><th class="text-center">People</th><th>Last message</th><th></th></tr></thead>
            <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td><div class="fw-semibold">{{ \Illuminate\Support\Str::limit($r->title, 60) }}</div>
                        <div class="small text-muted">@if ($r->live)<span class="badge bg-danger">LIVE</span> {{ optional($r->at)->format('d M, h:i a') }}@else Recorded @endif</div></td>
                    <td class="small">{{ $r->exams ?: '-' }}</td>
                    <td>@if ($r->open)<span class="badge bg-success">Open now</span>@elseif ($r->mode === 'off')<span class="badge bg-secondary">Off</span>@else<span class="badge bg-warning text-dark">{{ $r->mode === 'live' ? 'Live only' : 'Closed' }}</span>@endif</td>
                    <td class="text-center">{{ $r->total }}</td>
                    <td class="text-center">{{ $r->people }}</td>
                    <td class="small">{{ $r->last ? $r->last->diffForHumans() : '-' }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-primary mb-0" href="{{ url('admin/class-chats/' . $r->source . '/' . $r->id) }}"><i class="bi bi-chat-dots me-1"></i>Open</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No video has its chat on yet. Switch it on in the free class form (Class chat).</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
