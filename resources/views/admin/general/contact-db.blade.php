@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom">
            <div class="row align-items-center">
                <div class="col-md-4"><h3 class="mb-0">Contact Database</h3></div>
                <div class="col-md-8">
                    <form method="GET" class="row g-2 justify-content-md-end">
                        <div class="col-auto">
                            <input type="text" name="q" value="{{ $term }}" class="form-control form-control-sm"
                                placeholder="Name, email, mobile, college...">
                        </div>
                        <div class="col-auto"><button class="btn btn-sm btn-primary">Search</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if (session('message'))
                <div class="alert alert-success py-2">{{ session('message') }}</div>
            @endif
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>College</th>
                            <th>University</th>
                            <th>Course</th>
                            <th>Place / District</th>
                            <th>Created at</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->mobile }}</td>
                                <td>{{ $row->college ?? '—' }}</td>
                                <td>{{ $row->university ?? '—' }}</td>
                                <td>{{ $row->course ?? '—' }}</td>
                                <td>{{ trim(($row->place ?? '') . ' ' . ($row->district ?? '')) ?: '—' }}</td>
                                <td>{{ $row->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="text-end">
                                    <a title="Delete" href="{{ admin_general('contact-db/delete/' . $row->id) }}"
                                        onclick="return confirm('Delete this contact?')"
                                        class="btn btn-sm btn-danger btn-circle"><i class="bi bi-trash"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $list->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
@endsection
