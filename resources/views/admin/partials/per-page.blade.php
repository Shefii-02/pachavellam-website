@php $sizes = collect([10, 25, 30, 50, 100, $p->perPage()])->unique()->sort()->values(); @endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <span class="small text-muted">Showing {{ $p->firstItem() ?? 0 }}–{{ $p->lastItem() ?? 0 }} of {{ number_format($p->total()) }}</span>
    <form method="GET" class="d-flex align-items-center gap-2 mb-0">
        @foreach (request()->except(['per_page', 'page']) as $k => $v)
            @if (is_scalar($v))<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
        @endforeach
        <label class="small text-muted mb-0">Show</label>
        <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            @foreach ($sizes as $n)<option value="{{ $n }}" @selected($p->perPage() == $n)>{{ $n }}</option>@endforeach
        </select>
    </form>
</div>