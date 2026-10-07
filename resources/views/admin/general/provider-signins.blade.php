@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <div class="card-header bg-transparent border-bottom">
            <div class="row align-items-center">
                <div class="col-md-5"><h3 class="mb-0">{{ $title }}</h3></div>
                <div class="col-md-7">
                    <form method="GET" class="row g-2 justify-content-md-end">
                        <div class="col-auto">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Name, email or mobile">
                        </div>
                        <div class="col-auto"><button class="btn btn-sm btn-primary">Search</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>{{ $idLabel }}</th>
                            <th>Signed up at</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name ?: '—' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->mobile ?: '—' }}</td>
                                <td class="text-break" style="max-width:220px">{{ $user->{$column} }}</td>
                                <td>{{ $user->created_at?->format('d M Y, h:i A') }}</td>
                                <td class="text-end">
                                    <a title="Delete user" href="{{ admin_general('users/delete/' . $user->id) }}"
                                       onclick="return confirm('Delete this user account?')" class="btn btn-sm btn-danger btn-circle">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $users->links() }}</div>
        </div>
    </div>
@endsection
