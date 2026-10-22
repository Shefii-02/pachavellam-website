@extends('admin.exam.course.workspace')
@section('course_body')
@php $done = collect($checklist)->where(1, true)->count(); @endphp

{{-- details --}}
<div class="card bg-transparent border rounded-3 mb-4">
    <div class="card-body">
        <div class="row g-3">
            @if ($course->cover_image)
                <div class="col-md-4"><img src="{{ asset('storage/' . $course->cover_image) }}" alt="" class="img-fluid rounded"></div>
            @endif
            <div class="{{ $course->cover_image ? 'col-md-8' : 'col-12' }}">
                <table class="table table-sm mb-0">
                    <tr><th class="text-muted fw-normal" style="width:150px">Exam type</th><td><span class="badge text-white" style="background:{{ $exam->color ?: '#6c757d' }}">{{ $exam->short_name ?: $exam->name }}</span></td></tr>
                    <tr><th class="text-muted fw-normal">Categories</th><td>@forelse ($categories as $cat)<span class="badge bg-primary me-1">{{ $cat->name }}</span>@empty — @endforelse</td></tr>
                    <tr><th class="text-muted fw-normal">Level · Language</th><td>{{ \App\Models\ExamCourse::LEVELS[$course->level] ?? $course->level }} · {{ \App\Models\ExamCourse::LANGUAGES[$course->language] ?? $course->language }}</td></tr>
                    <tr><th class="text-muted fw-normal">Price</th><td>
                        @if ($course->isFree())<span class="badge bg-success">Free</span>
                        @else <strong>₹{{ number_format($course->effectivePrice()) }}</strong>@if ($course->effectivePrice() < $course->price) <s class="text-muted">₹{{ number_format($course->price) }}</s>@endif @endif
                    </td></tr>
                    <tr><th class="text-muted fw-normal">Validity</th><td>{{ $course->validityLabel() }}</td></tr>
                    <tr><th class="text-muted fw-normal">Runs</th><td>{{ $course->starts_on ? $course->starts_on->format('d M Y') : '—' }} → {{ $course->ends_on ? $course->ends_on->format('d M Y') : '—' }}@if ($course->duration_hours) · {{ $course->duration_hours }} hours @endif</td></tr>
                    <tr><th class="text-muted fw-normal">Certificate</th><td>{{ $course->certificate_enabled ? 'Yes' : 'No' }}</td></tr>
                    <tr><th class="text-muted fw-normal">Tags</th><td>@forelse ($course->tags ?? [] as $t)<span class="badge bg-light text-dark border me-1">#{{ $t }}</span>@empty — @endforelse</td></tr>
                </table>
            </div>
        </div>
        @if (!empty($course->highlights))
            <h6 class="mt-3">What you will learn</h6>
            <ul class="mb-0">@foreach ($course->highlights as $h)<li>{{ $h }}</li>@endforeach</ul>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- ready to publish? --}}
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom d-flex justify-content-between">
                <h5 class="mb-0">Ready to publish?</h5>
                <span class="badge bg-{{ $done === count($checklist) ? 'success' : 'warning text-dark' }}">{{ $done }} / {{ count($checklist) }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @foreach ($checklist as [$label, $ok, $hint])
                    <li class="list-group-item d-flex align-items-start">
                        <i class="bi bi-{{ $ok ? 'check-circle-fill text-success' : 'circle text-muted' }} me-2 mt-1"></i>
                        <div><div class="{{ $ok ? '' : 'fw-bold' }}">{{ $label }}</div>@unless ($ok)<div class="small text-muted">{{ $hint }}</div>@endunless</div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- staff --}}
    <div class="col-lg-6">
        <div class="card bg-transparent border rounded-3 h-100">
            <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Staff &amp; teachers</h5></div>
            <ul class="list-group list-group-flush">
                @forelse ($members as $m)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>{{ $m->name }}<div class="small text-muted">{{ $m->email }}</div></div>
                        <div class="text-end">
                            <span class="badge bg-{{ $m->pivot->member_role === 'teacher' ? 'info' : 'secondary' }} text-white">{{ ucfirst($m->pivot->member_role) }}</span>
                            <div class="small text-muted">{{ $m->pivot->can_edit ? 'can edit' : 'view only' }}</div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-muted small">Nobody is assigned yet. Only Admins can see this course.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- features --}}
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">What this course includes</h5></div>
    <div class="list-group list-group-flush">
        @forelse ($navFeatures as $n)
            @php
                $f = $n->feature;
                $catIds = json_decode($f->pivot->category_ids ?? '[]', true) ?: [];
                $kindLabel = ['tree' => 'content', 'exam' => 'exams', 'classes' => 'classes', 'flag' => 'related'][$f->content_kind] ?? '';
            @endphp
            <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
                <div>
                    @include('admin.partials.feature-icon', ['feature' => $f, 'size' => 26])<strong>{{ $f->name }}</strong>
                    <span class="badge bg-light text-dark border ms-1">{{ $kindLabel }}</span>
                    @if ($f->content_kind === 'exam')
                        <span class="badge bg-light text-dark border ms-1">Common · all categories</span>
                    @endif
                    <div class="small text-muted ms-4">
                        @if ($f->content_kind === 'tree') {{ $n->count }} file(s) / article(s)
                        @elseif ($f->content_kind === 'exam') {{ $n->count }} exam(s) / entries
                        @elseif ($f->content_kind === 'classes') {{ $n->count }} class(es)
                        @elseif ($n->hasBank) {{ $n->count }} attached
                        @else Included in the course @endif
                    </div>
                </div>
                <a href="{{ $n->url }}" class="btn btn-sm btn-primary mb-0">{{ $canEdit ? 'Manage' : 'View' }} <i class="bi bi-chevron-right"></i></a>
            </div>
        @empty
            <div class="list-group-item text-muted">No features ticked yet.@if ($isAdmin) <a href="{{ $courseUrl }}/edit">Edit the course</a> to choose them.@endif</div>
        @endforelse
    </div>
</div>
@endsection
