{{-- A grid card: thumbnail, exam type, date and title. Needs $item (ContentItem with exams) and $href; optional $icon. --}}
@php
    $thumb = $item->image ? Storage::url('files/' . $item->image) : null;
    $date = $item->published_at ?? $item->created_at;
    $first = $item->exams->first();
@endphp
<a class="cc-card" href="{{ $href }}" data-x="{{ $item->exams->pluck('id')->implode(' ') }}">
    <div class="cc-th" style="{{ $thumb ? "background-image:url('" . $thumb . "')" : '' }}">
        @unless ($thumb)<i class="bi {{ $icon ?? 'bi-megaphone' }}"></i>@endunless
        @if ($first)<span class="cc-ex" style="background:{{ $first->color ?: '#2563eb' }}">{{ $first->short_name ?: $first->name }}@if ($item->exams->count() > 1) +{{ $item->exams->count() - 1 }}@endif</span>@endif
        @if ($item->is_pinned)<span class="cc-pin"><i class="bi bi-pin-angle-fill"></i></span>@endif
    </div>
    <div class="cc-bd">
        <div class="cc-dt"><i class="bi bi-calendar3"></i> {{ optional($date)->format('d M Y') }}</div>
        <div class="cc-tt">{{ $item->title }}</div>
        @if (($excerpt ?? false) && $item->excerpt())<div class="cc-ex2" style="font-size:.8rem;color:var(--pv-muted,#6b7280);margin-top:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">{!! e($item->excerpt()) !!}</div>@endif
    </div>
</a>
