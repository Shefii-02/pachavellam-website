@extends($layout)
@section('content2')
@php $editing = (bool) $row; @endphp
<div class="card bg-transparent border rounded-3">
    <div class="card-header bg-transparent border-bottom">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'Add' }} · {{ $moduleLabel }} {{ $config['label'] }}</h3>
    </div>
    <div class="card-body">
        <form action="{{ $editing ? $baseUrl . '/' . $row->id : $baseUrl }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="row g-3">
                @foreach ($config['fields'] as $f)
                    @php
                        $name = $f['name'];
                        $value = old($name, $row->{$name} ?? null);
                        $wide = in_array($f['type'], ['textarea']);
                    @endphp

                    @if ($f['type'] === 'checkbox')
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="{{ $name }}" id="f_{{ $name }}" value="1" {{ $value ? 'checked' : '' }}>
                                <label class="form-check-label" for="f_{{ $name }}">{{ $f['label'] }}</label>
                            </div>
                        </div>
                    @else
                        <div class="{{ $wide ? 'col-12' : 'col-md-6' }}">
                            <label class="form-label">{{ $f['label'] }} @if (!empty($f['required']))<span class="text-danger">*</span>@endif</label>

                            @switch($f['type'])
                                @case('textarea')
                                    <textarea class="form-control" name="{{ $name }}" rows="5">{{ $value }}</textarea>
                                    @break
                                @case('select')
                                    <select class="form-control" name="{{ $name }}" {{ !empty($f['required']) ? 'required' : '' }}>
                                        @foreach ($f['options'] as $k => $label)
                                            <option value="{{ $k }}" {{ $value == $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('category')
                                    <select class="form-control" name="{{ $name }}">
                                        <option value="">— None —</option>
                                        @foreach ($categoryOptions as $id => $label)
                                            <option value="{{ $id }}" {{ $value == $id ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @break
                                @case('image')
                                @case('file')
                                    <input class="form-control" type="file" name="{{ $name }}"
                                        accept="{{ $f['type'] === 'image' ? 'image/*' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.jpg,.jpeg,.png' }}"
                                        {{ !empty($f['required']) && !($editing && $row->{$name}) ? 'required' : '' }}>
                                    <div class="form-text">{{ $f['type'] === 'image' ? 'JPG, PNG, WEBP or SVG, max 4 MB.' : 'PDF, Office, ZIP or image, max 20 MB.' }}</div>
                                    @if ($editing && $row->{$name})
                                        <div class="mt-2 d-flex align-items-center gap-3">
                                            @if ($f['type'] === 'image')
                                                <img src="{{ asset('storage/' . $row->{$name}) }}" alt="" style="height:56px;max-width:140px;object-fit:cover;border-radius:6px">
                                            @else
                                                <a href="{{ asset('storage/' . $row->{$name}) }}" target="_blank"><i class="bi bi-file-earmark-arrow-down"></i> Current file</a>
                                            @endif
                                            @if (empty($f['required']))
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="remove_{{ $name }}" value="1" id="rm_{{ $name }}">
                                                    <label class="form-check-label" for="rm_{{ $name }}">Remove</label>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @break
                                @default
                                    <input class="form-control"
                                        type="{{ ['url' => 'url', 'number' => 'number', 'date' => 'date'][$f['type']] ?? 'text' }}"
                                        name="{{ $name }}" value="{{ $f['type'] === 'date' && $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : $value }}"
                                        {{ !empty($f['required']) ? 'required' : '' }}>
                            @endswitch

                            @error($name)<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    @endif
                @endforeach

                <div class="col-md-3">
                    <label class="form-label">Position</label>
                    <input class="form-control" type="number" min="0" name="position" value="{{ old('position', $row->position ?? 0) }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="f_status"
                            {{ old('status', $editing ? $row->status : true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="f_status">Show</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success mb-0"><i class="bi bi-check me-1"></i> Save</button>
                <a href="{{ $baseUrl }}" class="btn btn-light mb-0">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
