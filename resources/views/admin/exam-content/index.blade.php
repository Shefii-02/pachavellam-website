@extends($layout)
@section('content2')
@php
    $fields = collect($config['fields'])->keyBy('name');
@endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <div class="row align-items-center g-2">
            <div class="col-md-5">
                <h3 class="mb-0"><i class="bi {{ $config['icon'] }} me-2"></i>{{ $moduleLabel }} · {{ $config['label'] }}</h3>
            </div>
            <div class="col-md-7">
                <form method="GET" class="row g-2 justify-content-md-end">
                    <div class="col-auto">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search title...">
                    </div>
                    @if ($categoryField)
                        <div class="col-auto">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All categories</option>
                                @foreach ($categoryOptions as $id => $label)
                                    <option value="{{ $id }}" @selected(request('category') == $id)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-auto"><button class="btn btn-sm btn-light">Filter</button></div>
                    <div class="col-auto"><a href="{{ $baseUrl }}/create" class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>Add New</a></div>
                </form>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                <thead>
                    <tr>
                        <th class="border-0">#</th>
                        @foreach ($config['list'] as $col)
                            <th class="border-0">{{ $fields[$col]['label'] }}</th>
                        @endforeach
                        <th class="border-0">Position</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 rounded-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            @foreach ($config['list'] as $col)
                                @php $f = $fields[$col]; $val = $row->{$col}; @endphp
                                <td>
                                    @switch($f['type'])
                                        @case('image')
                                            @if ($val)<img src="{{ asset('storage/' . $val) }}" alt="" style="height:40px;max-width:90px;object-fit:cover;border-radius:6px">@else — @endif
                                            @break
                                        @case('file')
                                            @if ($val)<a href="{{ asset('storage/' . $val) }}" target="_blank"><i class="bi bi-file-earmark-arrow-down"></i> Open</a>@else — @endif
                                            @break
                                        @case('url')
                                            @if ($val)<a href="{{ $val }}" target="_blank" rel="noopener">{{ \Illuminate\Support\Str::limit($val, 30) }}</a>@else — @endif
                                            @break
                                        @case('category')
                                            {{ $categoryOptions[$val] ?? '—' }}
                                            @break
                                        @case('select')
                                            <span class="badge bg-info text-white">{{ $f['options'][$val] ?? $val }}</span>
                                            @break
                                        @case('checkbox')
                                            {!! $val ? '<span class="badge bg-warning text-dark">Yes</span>' : '—' !!}
                                            @break
                                        @case('date')
                                            {{ $val ? \Carbon\Carbon::parse($val)->format('d M Y') : '—' }}
                                            @break
                                        @default
                                            {{ \Illuminate\Support\Str::limit((string) $val, 60) ?: '—' }}
                                    @endswitch
                                </td>
                            @endforeach
                            <td>{{ $row->position }}</td>
                            <td>
                                @if ($row->status)
                                    <span class="badge rounded-pill bg-orange text-white">Show</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary text-white">Hide</span>
                                @endif
                            </td>
                            <td>
                                <a title="Edit" href="{{ $baseUrl }}/{{ $row->id }}/edit" class="btn btn-sm btn-circle btn-info"><i class="bi bi-pencil"></i></a>
                                <form action="{{ $baseUrl }}/{{ $row->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete" class="btn btn-sm btn-circle btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($config['list']) + 4 }}" class="text-center text-muted py-4">Nothing added yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $list->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
