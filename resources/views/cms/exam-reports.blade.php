@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Reported errors</h3>
            <div>
                <a class="btn btn-sm {{ $status === 'open' ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ url('admin/kpsc/exam-reports?status=open') }}">Open</a>
                <a class="btn btn-sm {{ $status === 'resolved' ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ url('admin/kpsc/exam-reports?status=resolved') }}">Resolved</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table align-middle">
                <thead><tr><th>Exam</th><th>Q</th><th>Type</th><th>Message</th><th>Student</th><th>When</th><th></th></tr></thead>
                <tbody>
                @forelse ($reports as $r)
                    <tr>
                        <td>{{ $r->exam->examtitle ?? ('#' . $r->exam_id) }}<br><small class="text-muted">{{ $r->exam->exam_date ?? '' }}</small></td>
                        <td>{{ $r->question_no ?: '-' }}</td>
                        <td>{{ $r->type }}</td>
                        <td style="max-width:340px;white-space:pre-line">{{ $r->message }}</td>
                        <td>{{ $r->user->name ?? '-' }}<br><small class="text-muted">{{ $r->user->mobile ?? '' }}</small></td>
                        <td class="small">{{ $r->created_at->format('d M, h:i a') }}</td>
                        <td><a class="btn btn-sm btn-success" href="{{ url('admin/kpsc/exam-reports/toggle/' . $r->id) }}">{{ $r->status === 'open' ? 'Resolve' : 'Reopen' }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">Nothing here.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $reports->links() }}
        </div>
    </div>
@endsection
