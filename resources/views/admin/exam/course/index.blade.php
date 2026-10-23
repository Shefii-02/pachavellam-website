@extends($layout)
@section('content2')
@php $url = fn ($p = '') => $base . ($p !== '' ? '/' . ltrim($p, '/') : ''); @endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0">{{ $exam->short_name ?: $exam->name }} Courses</h3>
        @unless ($isAdmin)<div class="small text-muted">You see the courses you have been given access to.</div>@endunless
    </div>
    @if ($isAdmin)
        <a href="{{ $url('create') }}" class="btn btn-sm btn-primary mb-0"><i class="bi bi-plus me-1"></i>New Course</a>
    @endif
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search course title..."></div>
    <div class="col-6 col-md-2">
        <select name="status" class="form-select form-select-sm">
            <option value="">All statuses</option>
            @foreach (\App\Models\ExamCourse::STATUSES as $k => [$label])
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-md-2">
        <select name="type" class="form-select form-select-sm">
            <option value="">Free + paid</option>
            <option value="free" @selected(request('type') === 'free')>Free</option>
            <option value="paid" @selected(request('type') === 'paid')>Paid</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="category" class="form-select form-select-sm">
            <option value="">All categories</option>
            @foreach ($categories as $id => $label)
                <option value="{{ $id }}" @selected(request('category') == $id)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto d-flex gap-2">
        <button class="btn btn-sm btn-light mb-0">Filter</button>
        <a href="{{ $base }}" class="btn btn-sm btn-light mb-0">Reset</a>
    </div>
</form>

<div class="row g-3">
    @forelse ($courses as $c)
        @php [$stLabel, $stColor] = \App\Models\ExamCourse::STATUSES[$c->status] ?? [$c->status, 'secondary']; @endphp
        <div class="col-md-6 col-xl-4">
            <div class="card bg-transparent border rounded-3 h-100">
                @if ($c->cover_image)
                    <img src="{{ asset('storage/' . $c->cover_image) }}" class="card-img-top" alt="" style="height:130px;object-fit:cover">
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h5 class="mb-1"><a href="{{ $url($c->id) }}">{{ $c->title }}</a></h5>
                        <div class="text-end text-nowrap">
                            <span class="badge bg-{{ $stColor }}">{{ $stLabel }}</span>
                            @if ($c->is_featured)<span class="badge bg-warning text-dark">featured</span>@endif
                        </div>
                    </div>
                    <div class="mb-1">@foreach ($c->categories as $cat)<span class="badge bg-primary bg-opacity-75 me-1">{{ $cat->name }}</span>@endforeach</div>
                    @if ($c->short_description)<p class="small mb-2">{{ \Illuminate\Support\Str::limit($c->short_description, 100) }}</p>@endif
                    <div class="mb-2">
                        @if ($c->isFree())
                            <span class="badge bg-success">Free</span>
                        @else
                            <strong>₹{{ number_format($c->effectivePrice()) }}</strong>
                            @if ($c->effectivePrice() < $c->price)<s class="text-muted small">₹{{ number_format($c->price) }}</s>@endif
                        @endif
                        <span class="text-muted small ms-1">· {{ $c->validityLabel() }}</span>
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-4"><div class="border rounded py-1"><div class="fw-bold">{{ $c->features_count }}</div><div class="small text-muted">features</div></div></div>
                        <div class="col-4"><div class="border rounded py-1"><div class="fw-bold">{{ $c->nodes_count }}</div><div class="small text-muted">content items</div></div></div>
                        <div class="col-4"><div class="border rounded py-1"><div class="fw-bold">{{ $c->members_count }}</div><div class="small text-muted">staff</div></div></div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top d-flex gap-2">
                    <a href="{{ $url($c->id) }}" class="btn btn-sm btn-primary mb-0">Open</a>
                    @if ($isAdmin || $c->canBeEditedBy(auth()->user()))
                        <a href="{{ $url($c->id . '/edit') }}" class="btn btn-sm btn-light mb-0">Edit</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="text-center text-muted py-5">
            {{ $isAdmin ? 'No courses yet. Create the first one.' : 'No course has been assigned to you yet.' }}
        </div></div>
    @endforelse
</div>
<div class="mt-3">@include('admin.partials.per-page', ['p' => $courses])
{{ $courses->links('pagination::bootstrap-5') }}</div>
@endsection
