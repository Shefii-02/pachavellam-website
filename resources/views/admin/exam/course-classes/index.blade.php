@extends('admin.exam.course.workspace')
@section('course_body')
@include('admin.question-library._alerts')
@php $otherCount = $otherCourses->count(); @endphp
<div class="card bg-transparent border rounded-3 mb-3">
    <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="mb-0">@include('admin.partials.feature-icon', ['feature' => $feature, 'size' => 30]){{ $feature->name }}</h4>
            <div class="small text-muted">Classes come from the <a href="{{ $libraryUrl }}">class library</a> (YouTube / CDN links, cloud storage). The same class can be in many courses.</div>
        </div>
        @if ($canEdit)
            <div class="d-flex gap-2">
                <a href="{{ $manageUrl }}/add" class="btn btn-sm btn-success mb-0"><i class="bi bi-collection-play me-1"></i>From class library</a>
                <button class="btn btn-sm btn-primary mb-0" data-bs-toggle="collapse" data-bs-target="#quickBox"><i class="bi bi-plus me-1"></i>New class by link</button>
            </div>
        @endif
    </div>
    @if ($canEdit)
    <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="quickBox">
        <form action="{{ $manageUrl }}/quick" method="POST" class="card-body border-bottom">
            @csrf
            <div class="row g-2">
                <div class="col-md-5"><label class="form-label small">Class title</label><input type="text" name="title" class="form-control form-control-sm" required maxlength="250" value="{{ old('title') }}"></div>
                <div class="col-md-7"><label class="form-label small">Link (YouTube unlisted / CDN)</label><input type="text" name="url" class="form-control form-control-sm" required placeholder="https://…" value="{{ old('url') }}"></div>
                <div class="col-md-3"><label class="form-label small">Length</label><input type="text" name="duration" class="form-control form-control-sm" placeholder="1:25:30" value="{{ old('duration') }}"></div>
                <div class="col-md-4"><label class="form-label small">Teacher</label><input type="text" name="instructor" class="form-control form-control-sm" value="{{ old('instructor') }}"></div>
                <div class="col-md-3"><label class="form-label small">Class date</label><input type="date" name="class_date" class="form-control form-control-sm" value="{{ old('class_date') }}"></div>
                @include('admin.exam.partials.also-courses', ['otherCourses' => $otherCourses, 'what' => 'class'])
                <div class="col-12 d-flex gap-2 align-items-center"><button class="btn btn-sm btn-primary mb-0">Save class</button>
                    <span class="small text-muted">It is saved in the class library too. Add more details (rules, notes) there.</span></div>
            </div>
        </form>
    </div>
    @endif
    <div class="list-group list-group-flush">
        @forelse ($links as $i => $link)
            @php $c = $link->libraryClass; @endphp
            <div class="list-group-item bg-transparent">
                <div class="d-flex gap-3 align-items-center flex-wrap">
                    <div class="text-muted" style="width:26px">{{ $i + 1 }}.</div>
                    @if ($t = $c->thumbnailUrl())<img src="{{ $t }}" alt="" style="width:80px;height:45px;object-fit:cover" class="rounded border">@endif
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $c->title }}
                            @if ($c->status !== 'published')<span class="badge bg-warning text-dark">{{ $c->status }} - hidden from students</span>@endif
                            @if ($link->is_preview)<span class="badge bg-info">free preview</span>@endif</div>
                        <div class="small text-muted">{{ $c->sourceLabel() }} · {{ $c->durationLabel() }}{{ $c->instructor ? ' · ' . $c->instructor : '' }}{{ $c->class_date ? ' · ' . $c->class_date->format('d M Y') : '' }} · {{ $c->rulesSummary() }}
                            @if ($link->unlock_at) · unlocks {{ $link->unlock_at->format('d M Y H:i') }}@endif</div>
                    </div>
                    @if ($canEdit)
                        <div class="text-nowrap">
                            <form action="{{ $manageUrl }}/{{ $link->id }}/move" method="POST" class="d-inline">@csrf
                                <button name="dir" value="up" class="btn btn-sm btn-light mb-0" @disabled($i === 0)><i class="bi bi-arrow-up"></i></button>
                                <button name="dir" value="down" class="btn btn-sm btn-light mb-0" @disabled($i === $links->count() - 1)><i class="bi bi-arrow-down"></i></button></form>
                            <button class="btn btn-sm btn-light mb-0" data-bs-toggle="collapse" data-bs-target="#opt{{ $link->id }}" title="Unlock time / preview"><i class="bi bi-sliders"></i></button>
                            <a href="{{ $libraryUrl }}/{{ $c->id }}/edit" class="btn btn-sm btn-light mb-0" title="Edit class details in the library"><i class="bi bi-pencil"></i></a>
                            <form action="{{ $manageUrl }}/{{ $link->id }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this class from the course?')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-x-lg"></i></button></form>
                        </div>
                    @endif
                </div>
                @if ($canEdit)
                <div class="collapse mt-2" id="opt{{ $link->id }}">
                    <form action="{{ $manageUrl }}/{{ $link->id }}" method="POST" class="row g-2 align-items-end">@csrf @method('PUT')
                        <div class="col-md-4"><label class="form-label small mb-0">Unlock time (in this course)</label><input type="datetime-local" name="unlock_at" class="form-control form-control-sm" value="{{ $link->unlock_at?->format('Y-m-d\TH:i') }}"></div>
                        <div class="col-md-4"><div class="form-check"><input type="hidden" name="is_preview" value="0"><input class="form-check-input" type="checkbox" name="is_preview" value="1" id="pv{{ $link->id }}" @checked($link->is_preview)><label class="form-check-label small" for="pv{{ $link->id }}">Free preview (open to everyone)</label></div></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-primary mb-0">Save</button></div>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="list-group-item bg-transparent text-center text-muted py-4">No classes in this course yet.@if ($canEdit) Use "From class library" or "New class by link".@endif</div>
        @endforelse
    </div>
</div>
@endsection
