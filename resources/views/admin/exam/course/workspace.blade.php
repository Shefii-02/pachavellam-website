@extends($layout)
@section('content2')
@php
    [$stLabel, $stColor] = \App\Models\ExamCourse::STATUSES[$course->status] ?? [$course->status, 'secondary'];
    $here = request()->path();
    $pathOf = fn ($url) => ltrim((string) parse_url($url, PHP_URL_PATH), '/');
    $overviewActive = $here === $pathOf($courseUrl);
    $editActive = $here === $pathOf($courseUrl) . '/edit';
@endphp

{{-- header --}}
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ $listUrl }}" class="small"><i class="bi bi-arrow-left"></i> All {{ $exam->short_name ?: $exam->name }} courses</a>
        <h3 class="mb-0">
            {{ $course->title }}
            <span class="badge bg-{{ $stColor }} fs-6">{{ $stLabel }}</span>
            @if ($course->is_featured)<span class="badge bg-warning text-dark fs-6">featured</span>@endif
        </h3>
        @if ($course->short_description)<div class="text-muted small">{{ $course->short_description }}</div>@endif
    </div>
    @if ($canEdit)
        <div class="d-flex gap-2">
            <form action="{{ $courseUrl }}/publish" method="POST">
                @csrf
                <button class="btn btn-sm btn-{{ $course->status === 'published' ? 'outline-secondary' : 'success' }} mb-0">{{ $course->status === 'published' ? 'Move to draft' : 'Publish' }}</button>
            </form>
            @if ($isAdmin)
                <form action="{{ $courseUrl }}" method="POST" onsubmit="return confirm('Delete this course with ALL its content and files? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger mb-0" title="Delete course"><i class="bi bi-trash"></i></button>
                </form>
            @endif
        </div>
    @else
        <span class="badge bg-secondary">view only</span>
    @endif
</div>

<div class="row g-4">
    {{-- side menu --}}
    <div class="col-lg-3">
        <div class="card bg-transparent border rounded-3">
            <div class="list-group list-group-flush rounded-3">
                <a href="{{ $courseUrl }}" class="list-group-item list-group-item-action {{ $overviewActive ? 'active' : '' }}">
                    <i class="bi bi-grid me-2"></i>Overview
                </a>
                @if ($canEdit)
                    <a href="{{ $courseUrl }}/edit" class="list-group-item list-group-item-action {{ $editActive ? 'active' : '' }}">
                        <i class="bi bi-pencil-square me-2"></i>Edit details
                    </a>
                @endif

                <div class="list-group-item small text-uppercase text-muted pt-3 pb-1 border-bottom-0">Features</div>
                @forelse ($navFeatures as $n)
                    @php $active = request()->is($pathOf($n->url) . '*'); @endphp
                    <a href="{{ $n->url }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $active ? 'active' : '' }}">
                        <span>
                            @include('admin.partials.feature-icon', ['feature' => $n->feature, 'size' => 20])
                            {{ $n->feature->name }}
                        </span>
                        @if ($n->feature->content_kind === 'flag' && !$n->hasBank)
                            <span class="badge bg-light text-dark border" title="Included in the course">included</span>
                        @else
                            <span class="badge bg-{{ $n->count ? 'primary' : 'secondary' }} rounded-pill">{{ $n->count }}</span>
                        @endif
                    </a>
                @empty
                    <div class="list-group-item small text-muted">No features ticked yet.@if ($isAdmin) <a href="{{ $courseUrl }}/edit">Choose them</a>.@endif</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- the page --}}
    <div class="col-lg-9">
        @yield('course_body')
    </div>
</div>
@endsection
