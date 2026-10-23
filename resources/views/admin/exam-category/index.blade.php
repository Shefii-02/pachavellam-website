@extends($layout)
@section('content2')
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h3 class="mb-1">{{ $moduleLabel }} Categories</h3>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ $baseUrl }}">Root</a></li>
                        @foreach ($breadcrumb as $crumb)
                            <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                @if ($loop->last) {{ $crumb->name }}
                                @else <a href="{{ $baseUrl }}?parent={{ $crumb->id }}">{{ $crumb->name }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            </div>
            <div class="col-md-5 text-end">
                @if ($parent)
                    <a href="{{ $parent->parent_id ? $baseUrl . '?parent=' . $parent->parent_id : $baseUrl }}"
                        class="btn btn-sm btn-light me-1"><i class="bi bi-arrow-left me-1"></i>Back</a>
                @endif
                <a href="{{ $baseUrl }}/create{{ $parent ? '?parent=' . $parent->id : '' }}"
                    class="btn btn-sm btn-primary"><i class="bi bi-plus me-1"></i>
                    {{ $parent ? 'Add Sub Category' : 'Add Category' }}</a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive border-0">
            <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                <thead>
                    <tr>
                        <th class="border-0">#</th>
                        <th class="border-0">Name</th>
                        <th class="border-0">Sub Categories</th>
                        <th class="border-0">Position</th>
                        <th class="border-0">Status</th>
                        <th class="border-0 rounded-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($list as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($item->image)<img src="{{ asset('storage/' . $item->image) }}" alt="" class="me-2" style="height:32px;width:32px;object-fit:cover;border-radius:6px">@endif
                                <a href="{{ $baseUrl }}?parent={{ $item->id }}">{{ $item->name }}</a>
                                @if ($item->is_featured)<span class="badge bg-warning text-dark ms-1">Featured</span>@endif
                            </td>
                            <td>
                                <a href="{{ $baseUrl }}?parent={{ $item->id }}" class="badge bg-info text-white">
                                    {{ $item->children_count }} <i class="bi bi-chevron-right"></i>
                                </a>
                            </td>
                            <td>{{ $item->position }}</td>
                            <td>
                                @if ($item->status)
                                    <span class="badge rounded-pill bg-orange text-white">Show</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary text-white">Hide</span>
                                @endif
                            </td>
                            <td>
                                <a title="Add sub category" href="{{ $baseUrl }}/create?parent={{ $item->id }}"
                                    class="btn btn-sm btn-circle btn-success"><i class="bi bi-plus"></i></a>
                                <a title="Edit" href="{{ $baseUrl }}/{{ $item->id }}/edit"
                                    class="btn btn-sm btn-circle btn-info"><i class="bi bi-pencil"></i></a>
                                <form action="{{ $baseUrl }}/{{ $item->id }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Delete this category and all of its sub categories?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete" class="btn btn-sm btn-circle btn-danger">
                                        <i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No {{ $parent ? 'sub categories' : 'categories' }} yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
