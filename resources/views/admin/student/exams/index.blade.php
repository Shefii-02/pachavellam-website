@extends('admin.student.section')
@section('content2')
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h3 class="mb-0">Competitive Exams</h3>
                <div class="small text-muted">How many students picked each exam type. Exam types are added and edited in General → Exam Types.</div>
            </div>
            <div class="col-md-5 text-end">
                @if (auth()->user()->type === 'Admin')<a href="{{ admin_general('exam-types') }}" class="btn btn-sm btn-primary"><i class="bi bi-gear me-1"></i>Manage exam types</a>@endif
            </div>
        </div>
    </div>
    <div class="card-body">
        <p class="small text-muted">{{ number_format($withInterest) }} of {{ number_format($students) }} students have chosen at least one interest.</p>
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                <thead>
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Exam</th>
                        <th class="border-0">Slug</th>
                        <th class="border-0">Interested students</th>
                        <th class="border-0">Performance data</th>
                        <th class="border-0">Position</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 rounded-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exams as $e)
                        @php $link = $e->interestedUrl(); @endphp
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>
                                <span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>
                                <span class="ms-1">{{ $e->name }}</span>
                            </td>
                            <td><code>{{ $e->slug }}</code></td>
                            <td><a href="{{ $link }}"><strong>{{ number_format($counts[$e->id] ?? 0) }}</strong> students <i class="bi bi-chevron-right"></i></a></td>
                            <td>{!! $e->performance_source ? '<span class="badge bg-success">'.e($e->performance_source).'</span>' : '<span class="text-muted small">not connected</span>' !!}</td>
                            <td>{{ $e->position }}</td>
                            <td>
                                @if ($e->status)<span class="badge rounded-pill bg-orange text-white">Show</span>
                                @else<span class="badge rounded-pill bg-secondary text-white">Hide</span>@endif
                            </td>
                            <td><a href="{{ $link }}" class="btn btn-sm btn-primary">Students</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No exams yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
