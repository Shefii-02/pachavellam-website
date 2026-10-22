@php $size = $size ?? 24; @endphp
@if ($feature->image)
    <img src="{{ asset('storage/' . $feature->image) }}" alt="" class="me-2 align-middle" style="width:{{ $size }}px;height:{{ $size }}px;object-fit:contain;border-radius:4px">
@else
    <span class="d-inline-flex align-items-center justify-content-center me-2 align-middle bg-light text-muted border"
        style="width:{{ $size }}px;height:{{ $size }}px;border-radius:4px;font-size:{{ round($size * 0.5) }}px;font-weight:600">{{ mb_strtoupper(mb_substr($feature->name, 0, 1)) }}</span>
@endif
