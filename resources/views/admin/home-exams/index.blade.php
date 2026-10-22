@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-house-heart me-1"></i>Exams on the home page</h3>
            <div class="small text-muted">Tick the exam series you want as cards on the home page. Each card shows the active season's newest exams with Attempt buttons and a "See all" link to the series page. Cards are ordered by the number (smallest first).</div>
        </div>
    </div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif

    <form method="POST" action="{{ url('admin/home-exams') }}">@csrf
        <div class="table-responsive border rounded-3">
            <table class="table align-middle mb-0">
                <thead><tr><th>Exam type</th>@foreach ($series as $k => $label)<th class="text-center">{{ $label }}</th>@endforeach</tr></thead>
                <tbody>
                @foreach ($exams as $e)
                    <tr>
                        <td><span class="badge text-white me-1" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span><span class="small text-muted">{{ $e->name }}</span></td>
                        @foreach ($series as $k => $label)
                            @php $r = $rows[$e->id . ':' . $k] ?? null; @endphp
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div class="form-check form-switch mb-0"><input class="form-check-input" type="checkbox" role="switch" name="show[{{ $e->id }}][{{ $k }}]" value="1" id="s{{ $e->id }}{{ $k }}" {{ $r && $r->show_home ? 'checked' : '' }}><label class="form-check-label small" for="s{{ $e->id }}{{ $k }}">Show</label></div>
                                    <input type="number" min="0" max="9999" class="form-control form-control-sm" style="width:70px" name="pos[{{ $e->id }}][{{ $k }}]" value="{{ $r->position ?? 0 }}" title="Order on the home page">
                                </div>
                                <div class="small mt-1"><a href="{{ url($e->slug . '/' . $k) }}" target="_blank">open page</a></div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3"><button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button> <span class="small text-muted ms-2">A series with no exams yet is skipped on the home page until it has some.</span></div>
    </form>
</div>
@endsection
