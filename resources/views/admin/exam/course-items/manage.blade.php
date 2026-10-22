@extends('admin.exam.course.workspace')
@section('course_body')
@php $isExam = $feature->content_kind === 'exam'; @endphp

<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom">
        <h4 class="mb-0">@include('admin.partials.feature-icon', ['feature' => $feature, 'size' => 30]){{ $feature->name }}</h4>
        @if ($feature->description)<div class="small text-muted mt-1">{{ $feature->description }}</div>@endif
    </div>
    <div class="card-body">
        @if ($isExam)
            <div class="mb-2">
                @if ($scope === 'category')
                    <span class="badge bg-info text-white">Category based</span>
                    <span class="small">{{ implode(', ', $scopeNames) ?: 'no categories chosen' }}</span>
                @else
                    <span class="badge bg-light text-dark border">Common · all categories</span>
                @endif
                @if ($canEdit && $isAdmin)<a href="{{ $courseUrl }}/edit" class="small ms-2">change</a>@endif
            </div>
        @endif

        @unless ($cfg)
            <div class="alert alert-info mb-0 py-2 small">
                @if ($isExam)
                    No exam bank is connected to <strong>{{ $feature->name }}</strong> for {{ $exam->short_name ?: $exam->name }} yet, so there is nothing to pick here.
                    The course already includes the feature; exams can be attached once a bank is connected.
                @else
                    <strong>{{ $feature->name }}</strong> is included in this course. There is nothing to pick for {{ $exam->short_name ?: $exam->name }} yet.
                @endif
                <div class="text-muted">(Developers: connect a bank in <code>config/course_item_sources.php</code>.)</div>
            </div>
        @endunless
    </div>
</div>

@if ($cfg)
    {{-- in the course --}}
    <div class="card bg-transparent border rounded-3 mb-4">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
            <h5 class="mb-0">In this course</h5>
            <span class="badge bg-primary rounded-pill">{{ $rows->count() }}</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th style="width:50px">#</th><th>{{ $cfg['label'] }}</th><th>Status in the bank</th>@if ($canEdit)<th class="text-end">Action</th>@endif</tr></thead>
                <tbody>
                    @forelse ($rows as $i => $r)
                        <tr class="{{ $r->missing ? 'text-danger' : '' }}">
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $r->title }}@if ($r->subtitle)<div class="small text-muted">{{ $r->subtitle }}</div>@endif</td>
                            <td>
                                @if ($r->missing)<span class="badge bg-danger">removed</span>
                                @elseif ($r->published)<span class="badge bg-success">published</span>
                                @else<span class="badge bg-secondary" title="Students will not see it until it is published in the bank">draft</span>@endif
                            </td>
                            @if ($canEdit)
                                <td class="text-end">
                                    <form action="{{ $courseUrl }}/items/{{ $feature->slug }}/{{ $r->id }}" method="POST" class="d-inline" onsubmit="return confirm('Take this out of the course? The {{ strtolower($cfg['label']) }} itself is not deleted.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-circle btn-danger" title="Remove from the course"><i class="bi bi-x-lg"></i></button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Nothing added yet.{{ $canEdit ? ' Pick from the list below.' : '' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- pick from the bank --}}
    @if ($canEdit && $candidates)
        <div class="card bg-transparent border rounded-3">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">Add from the {{ strtolower($cfg['label']) }} list</h5>
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="q" value="{{ $term }}" class="form-control form-control-sm" placeholder="Search..." style="min-width:220px">
                        <button class="btn btn-sm btn-light mb-0">Search</button>
                        @if ($term)<a href="{{ $manageUrl }}" class="btn btn-sm btn-light mb-0">Clear</a>@endif
                    </form>
                </div>
            </div>
            <form action="{{ $courseUrl }}/items/{{ $feature->slug }}" method="POST">
                @csrf
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm align-middle mb-0 table-hover">
                        <thead><tr><th style="width:40px"><input type="checkbox" class="form-check-input" id="allBox"></th><th>{{ $cfg['label'] }}</th><th>Status in the bank</th></tr></thead>
                        <tbody>
                            @forelse ($candidates as $c)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input pick" name="ids[]" value="{{ $c->source_id }}"></td>
                                    <td>{{ $c->title }}@if ($c->subtitle)<div class="small text-muted">{{ $c->subtitle }}</div>@endif</td>
                                    <td>@if ($c->published)<span class="badge bg-success">published</span>@else<span class="badge bg-secondary">draft</span>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">{{ $term ? 'Nothing matches your search.' : 'Everything is already in the course.' }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @error('ids')<div class="text-danger small px-3 pt-2">{{ $message }}</div>@enderror
                @if (!empty($otherCourses) && $otherCourses->isNotEmpty())
                    <div class="row px-3 pt-2">@include('admin.exam.partials.also-courses', ['otherCourses' => $otherCourses, 'what' => 'selection'])</div>
                @endif
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <button class="btn btn-success btn-sm mb-0"><i class="bi bi-plus-circle me-1"></i>Add selected</button>
                    <div>{{ $candidates->links('pagination::bootstrap-5') }}</div>
                </div>
            </form>
        </div>
    @endif
@endif
@endsection

@section('scripts')
<script>
    (function () {
        var all = document.getElementById('allBox');
        if (all) { all.addEventListener('change', function () { document.querySelectorAll('.pick').forEach(function (b) { b.checked = all.checked; }); }); }
    })();
</script>
@endsection
