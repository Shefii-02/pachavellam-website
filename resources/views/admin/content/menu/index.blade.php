@extends('admin.main-layout')
@section('main')
@php $v = fn ($f, $d = '') => old($f, $edit->{$f} ?? $d); @endphp
<div class="page-content-wrapper border">
    <h3 class="mb-1"><i class="bi bi-list-nested me-1"></i>Menu setup</h3>
    <div class="small text-muted mb-3">The side menu of the website. Each item opens a link, a course, an exam type or a page (custom and prebuilt pages are listed). Items can have one level of sub items. Footer columns are managed under <a href="{{ url('admin/content/footer-links') }}">Website content &gt; Footer links</a>.</div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif

    <div class="card bg-transparent border rounded-3 mb-4"><div class="card-body">
        <h6>{{ $edit ? 'Edit menu item' : 'Add menu item' }}</h6>
        <form method="POST" action="{{ $edit ? url('admin/menu/' . $edit->id) : url('admin/menu') }}">
            @csrf @if ($edit) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Label <span class="text-danger">*</span></label><input class="form-control" name="label" maxlength="80" required value="{{ $v('label') }}">@error('label')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">Icon</label><input class="form-control" name="icon" maxlength="40" placeholder="bi-play-btn" value="{{ $v('icon') }}">@error('icon')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-3"><label class="form-label">Under</label>
                    <select name="parent_id" class="form-select"><option value="">Top level</option>@foreach ($parents as $pa)@if (!$edit || $pa->id !== $edit->id)<option value="{{ $pa->id }}" {{ (string) $v('parent_id') === (string) $pa->id ? 'selected' : '' }}>{{ $pa->label }}</option>@endif @endforeach</select>@error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-2"><label class="form-label">Order</label><input type="number" min="0" class="form-control" name="position" value="{{ $v('position', 0) }}"></div>
                <div class="col-12">@include('admin.content._landing-picker', ['type' => $edit->target_type ?? 'link', 'value' => $edit->target_value ?? ''])</div>
                <div class="col-md-8" data-landing-link><label class="form-label">Custom link</label><input class="form-control" id="landingLink" name="link_url" maxlength="500" value="{{ $v('link_url') }}" placeholder="https://… or /free-classes">@error('link_url')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                <div class="col-md-4 d-flex align-items-end gap-4">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="status" value="1" id="mst" {{ old('status', $edit->status ?? true) ? 'checked' : '' }}><label class="form-check-label" for="mst">Shown</label></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="new_tab" value="1" id="mnt" {{ old('new_tab', $edit->new_tab ?? false) ? 'checked' : '' }}><label class="form-check-label" for="mnt">New tab</label></div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2"><button class="btn btn-success mb-0">Save</button>@if ($edit)<a class="btn btn-light mb-0" href="{{ url('admin/menu') }}">Cancel</a>@endif</div>
        </form>
    </div></div>

    <div class="table-responsive"><table class="table table-dark-gray align-middle mb-0">
        <thead><tr><th>Item</th><th>Opens</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse ($items as $it)
                @foreach ([$it, ...$it->children] as $row)
                    <tr>
                        <td class="{{ $row->parent_id ? 'ps-4' : 'fw-bold' }}">@if ($row->parent_id)<i class="bi bi-arrow-return-right text-muted me-1"></i>@endif @if ($row->icon)<i class="bi {{ $row->icon }} me-1"></i>@endif{{ $row->label }}</td>
                        <td class="small text-muted">{{ $row->url() ?: '-' }}</td>
                        <td>{{ $row->position }}</td>
                        <td><form method="POST" action="{{ url('admin/menu/' . $row->id . '/toggle') }}">@csrf<button class="btn btn-sm mb-0 {{ $row->status ? 'btn-success-soft' : 'btn-light' }}">{{ $row->status ? 'Shown' : 'Hidden' }}</button></form></td>
                        <td class="text-end text-nowrap">
                            <a href="{{ url('admin/menu?edit=' . $row->id) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-pencil"></i></a>
                            <form class="d-inline" method="POST" action="{{ url('admin/menu/' . $row->id) }}" onsubmit="return confirm('Delete this menu item{{ $row->parent_id ? '' : ' and its sub items' }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft mb-0"><i class="bi bi-trash"></i></button></form>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No custom menu items yet. The standard menu is shown until you add some.</td></tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection
