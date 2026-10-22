@php
    $base = kpsc_cms('ca-daily-exam/seasons/' . $e->season_id);
@endphp
<tr>
    <td>{{ $e->slot_no }}</td>
    <td>
        <a href="{{ kpsc_cms('ca-daily-exam/edit/' . $e->id) }}">{{ $e->exam_title ?: (\App\Models\CaSeason::KINDS[$e->exam_kind] ?? '') . ' ' . $e->slot_no }}</a>
        @if ($e->exam_kind !== 'daily')<span class="badge bg-info text-white">{{ \App\Models\CaSeason::KINDS[$e->exam_kind] ?? $e->exam_kind }}</span>@endif
        @if ($e->period_start)
            <div class="small text-muted">covers {{ \Carbon\Carbon::parse($e->period_start)->format('d M') }} – {{ \Carbon\Carbon::parse($e->period_end)->format('d M') }}</div>
        @endif
        @if ($sources->isNotEmpty())
            <div class="small text-muted">from {{ $sources->map(fn ($s) => 'Day ' . $s->slot_no . ' (' . $s->question_count . ')')->implode(', ') }}</div>
        @endif
    </td>
    <td class="text-nowrap">{{ \Carbon\Carbon::parse($e->started_at)->format('D, d M Y') }}<div class="small text-muted">{{ \Carbon\Carbon::parse($e->started_at)->format('h:i A') }}</div></td>
    <td>{!! $e->exam_details_count ? number_format($e->exam_details_count) : '<span class="text-danger">0</span>' !!}</td>
    <td>{{ number_format($e->ca_exam_attened_count) }}</td>
    <td>
        <form action="{{ kpsc_cms('ca-daily-exam/exams/' . $e->id . '/publish') }}" method="POST">
            @csrf
            <button class="badge border-0 bg-{{ $e->status ? 'success' : 'secondary' }}" title="Click to {{ $e->status ? 'unpublish' : 'publish' }}">{{ $e->status ? 'Published' : 'Draft' }}</button>
        </form>
    </td>
    <td class="text-end text-nowrap">
        @if ($e->exam_kind !== 'daily')
            <a title="Add more questions from daily exams" href="{{ $base }}/build?kind={{ $e->exam_kind }}&exam_id={{ $e->id }}" class="btn btn-sm btn-circle btn-success"><i class="bi bi-magic"></i></a>
        @endif
        <a title="Edit / questions" href="{{ kpsc_cms('ca-daily-exam/edit/' . $e->id) }}" class="btn btn-sm btn-circle btn-info"><i class="bi bi-pencil"></i></a>
        <a title="Clear leaderboard" href="{{ kpsc_cms('ca-daily-exam/clear-leaderboard/' . $e->id) }}" onclick="return confirm('Clear all attempts and the leaderboard of this exam?')" class="btn btn-sm btn-circle btn-warning"><i class="bi bi-eraser"></i></a>
        <a title="Delete exam" href="{{ kpsc_cms('ca-daily-exam/delete-all/' . $e->id) }}" onclick="return confirm('Delete this exam, its questions and attempts?')" class="btn btn-sm btn-circle btn-danger"><i class="bi bi-trash"></i></a>
    </td>
</tr>
