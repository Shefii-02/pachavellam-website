@extends('admin.exam.course.workspace')
@section('course_body')
@php
    [$stLabel, $stColor] = \App\Models\CourseExam::STATUSES[$item->status];
    $multi = $sections->count() > 1;
    $here = url()->current();
    $total = $sections->sum(fn ($s) => $s->links->count());
    $duration = $item->computedDuration();
@endphp
@include('admin.question-library._alerts')

<div class="card bg-transparent border rounded-3 mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <a href="{{ $manageUrl }}" class="small"><i class="bi bi-arrow-left"></i> {{ $feature->name }}</a>
                <h4 class="mb-1 mt-1">{{ $item->title }} <span class="badge bg-{{ $stColor }} fs-6">{{ $stLabel }}</span></h4>
                <div class="small text-muted">
                    {{ \App\Models\CourseExam::MODES[$item->mode] ?? $item->mode }} ·
                    {{ $item->is_paid ? 'Paid' : 'Free' }} ·
                    {{ $item->max_attempts ? $item->max_attempts . ' attempt(s) per user' : 'unlimited attempts' }}
                    @if ($item->start_at || $item->end_at) · {{ $item->start_at?->format('d M Y H:i') ?? '…' }} → {{ $item->end_at?->format('d M Y H:i') ?? '…' }}@endif
                    @if ($item->unlock_at) · unlocks {{ $item->unlock_at->format('d M Y H:i') }}@endif
                </div>
            </div>
            @if ($canEdit)
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ $manageUrl }}/{{ $item->id }}/edit" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil me-1"></i>Settings</a>
                    @foreach (['published' => ['Publish', 'success'], 'draft' => ['Move to draft', 'secondary'], 'archived' => ['Archive', 'dark']] as $to => [$label, $color])
                        @continue($item->status === $to)
                        <form action="{{ $manageUrl }}/{{ $item->id }}/status" method="POST">@csrf<input type="hidden" name="status" value="{{ $to }}">
                            <button class="btn btn-sm btn-outline-{{ $color }} mb-0">{{ $label }}</button></form>
                    @endforeach
                    @if ($otherCourses->isNotEmpty())
                        <button type="button" class="btn btn-sm btn-light mb-0" data-bs-toggle="modal" data-bs-target="#copyModal"><i class="bi bi-copy me-1"></i>Copy to other courses</button>
                    @endif
                    <form action="{{ $manageUrl }}/{{ $item->id }}" method="POST" onsubmit="return confirm('Delete this exam? The questions stay in the question library.')">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-trash"></i></button></form>
                </div>
            @endif
        </div>

        @if ($isCa && $item->entry_date)<div class="small mt-1"><i class="bi bi-calendar-event me-1"></i>{{ $item->entry_date->format('l, d M Y') }}</div>@endif
        <div class="row g-3 mt-1 text-center">
            @foreach ([['Questions', $total], ['Sections', $sections->count()], ['Time', $duration ? $duration . ' min' : '—'], ['Avg / question', $item->averageSeconds() ? $item->averageSeconds() . ' sec' : '—'], ['Total mark', rtrim(rtrim(number_format($item->computedTotalMark(), 2), '0'), '.')], ['Marks', '+' . $item->right_mark . ' / −' . $item->negative_mark]] as [$k, $val])
                <div class="col-4 col-md-2"><div class="border rounded py-2"><div class="small text-muted">{{ $k }}</div><div class="fw-semibold">{{ $val }}</div></div></div>
            @endforeach
        </div>

        @if ($item->status === 'draft' && $total === 0 && !$isCa)
            <div class="alert alert-info py-2 mt-3 mb-0 small"><strong>Step 2:</strong> the sections are ready. Add questions to {{ $multi ? 'each section below' : 'the exam' }} (write them or take them from the question library), then publish.</div>
        @elseif ($item->status !== 'published' && $problems)
            <div class="alert alert-warning py-2 mt-3 mb-0 small"><strong>Before publishing:</strong> {{ implode(' ', $problems) }}</div>
        @elseif ($item->status === 'draft')
            <div class="alert alert-success py-2 mt-3 mb-0 small">Ready to publish. Only published exams are shown to students.</div>
        @endif
        @if ($isCa && $item->body)
            <div class="mt-3 border rounded p-3" style="max-height:420px;overflow:auto">{!! $item->body !!}</div>
        @endif
        @if ($item->instruction || $item->instruction_pdf)
            <div class="mt-3 small"><strong>Instructions:</strong> {!! nl2br(e(\App\Support\SafeHtml::text($item->instruction))) !!}
                @if ($item->instruction_pdf) <a href="{{ asset('storage/' . $item->instruction_pdf) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>@endif</div>
        @endif
    </div>
</div>

@if ($otherCourses->isNotEmpty())
<div class="modal fade" id="copyModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" action="{{ $manageUrl }}/{{ $item->id }}/copy" method="POST">@csrf
    <div class="modal-header"><h5 class="modal-title">Copy to other courses</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="row">@include('admin.exam.partials.also-courses', ['otherCourses' => $otherCourses, 'what' => 'one', 'note' => 'A separate copy (draft) is made in each ticked course: same settings, sections and questions.'])</div></div>
    <div class="modal-footer"><button class="btn btn-primary mb-0">Copy</button></div>
</form></div></div>
@endif

@foreach ($sections as $si => $section)
    @php
        [$rm, $nm] = $item->marksFor($section);
        $addQuery = http_build_query(['attach' => $item->id, 'section' => $section->id, 'back' => $here]);
    @endphp
    <div class="card bg-transparent border rounded-3 mb-3">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong>{{ $multi ? ($section->title ?: 'Section ' . ($si + 1)) : 'Questions' }}</strong>
                <span class="small text-muted ms-2">{{ $section->links->count() }} question(s)
                    @if ($multi) · {{ $section->duration_minutes ? $section->duration_minutes . ' min' : 'no time limit' }} @endif
                    · +{{ $rm }} / −{{ $nm }}</span>
                @if ($multi && ($section->instruction || $section->instruction_pdf))
                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit(\App\Support\SafeHtml::text($section->instruction), 140) }}
                        @if ($section->instruction_pdf)<a href="{{ asset('storage/' . $section->instruction_pdf) }}" target="_blank"><i class="bi bi-file-earmark-pdf"></i> PDF</a>@endif</div>
                @endif
            </div>
            @if ($canEdit)
                <div class="d-flex gap-2">
                    <a href="{{ $libraryUrl }}/questions/create?{{ $addQuery }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>Add question</a>
                    <a href="{{ $manageUrl }}/{{ $item->id }}/library?section={{ $section->id }}" class="btn btn-sm btn-success mb-0"><i class="bi bi-collection me-1"></i>From question library</a>
                </div>
            @endif
        </div>
        <div class="list-group list-group-flush">
            @forelse ($section->links as $li => $link)
                @php $q = $link->question; @endphp
                <div class="list-group-item bg-transparent d-flex gap-3 align-items-start">
                    <div class="text-muted" style="width:28px">{{ $li + 1 }}.</div>
                    <div class="flex-grow-1">
                        <div>@if ($q->hasImages())<i class="bi bi-image text-info me-1"></i>@endif{{ $q->plainQuestion(200) }}</div>
                        <div class="small text-muted">{{ $q->typeLabel() }} · Answer {{ $q->answerLabel() }}{{ $q->difficulty ? ' · ' . ucfirst($q->difficulty) : '' }} · {{ $q->texts->pluck('lang')->map('strtoupper')->implode('/') }}</div>
                    </div>
                    @if ($canEdit)
                        <div class="text-nowrap">
                            <form action="{{ $manageUrl }}/{{ $item->id }}/questions/{{ $link->id }}/move" method="POST" class="d-inline">@csrf
                                <button name="dir" value="up" class="btn btn-sm btn-light mb-0" @disabled($li === 0)><i class="bi bi-arrow-up"></i></button>
                                <button name="dir" value="down" class="btn btn-sm btn-light mb-0" @disabled($li === $section->links->count() - 1)><i class="bi bi-arrow-down"></i></button>
                            </form>
                            <a href="{{ $libraryUrl }}/questions/{{ $q->id }}/edit?{{ http_build_query(['back' => $here]) }}" class="btn btn-sm btn-light mb-0" title="Edit in the question library"><i class="bi bi-pencil"></i></a>
                            <form action="{{ $manageUrl }}/{{ $item->id }}/questions/{{ $link->id }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this question from the exam?')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger mb-0"><i class="bi bi-x-lg"></i></button></form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="list-group-item bg-transparent text-muted text-center py-4">No questions yet. Use "Add question" to write one, or "From question library".</div>
            @endforelse
        </div>
    </div>
@endforeach
@endsection
