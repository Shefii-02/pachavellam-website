{{-- One folder / file / article of a study-material tab. Needs $n (see MyCourseController::node). --}}
@if ($n->type === 'folder')
    <details class="mc-folder" @if ($depth < 1) open @endif>
        <summary><i class="bi bi-folder-fill" style="color:#f5b301"></i><span>{{ $n->title }}</span><small>{{ count($n->children) }}</small><i class="bi bi-chevron-down ch"></i></summary>
        <div class="mc-kids">
            @forelse ($n->children as $c)@include('kpsc.partials.my-course-node', ['n' => $c, 'depth' => $depth + 1])@empty<div class="mc-empty">Empty folder.</div>@endforelse
        </div>
    </details>
@elseif ($n->type === 'article')
    <details class="mc-folder">
        <summary><i class="bi bi-journal-text" style="color:#2563eb"></i><span>{{ $n->title }}</span><i class="bi bi-chevron-down ch"></i></summary>
        <div class="mc-kids"><div class="mc-article">{!! $n->body !!}</div>@if ($n->audio)<audio controls preload="none" src="{{ $n->audio }}" style="width:100%;margin-top:8px"></audio>@endif</div>
    </details>
@else
    @php $ic = \App\Models\ExamCourseNode::KIND_ICONS[$n->kind ?: ($n->url && !$n->kind ? 'link' : 'other')] ?? 'bi-file-earmark'; @endphp
    <a class="mc-file" href="{{ $n->url ?: '#' }}" target="_blank" rel="noopener"><i class="bi {{ $ic }}"></i><span>{{ $n->title }}</span>@if ($n->size)<small>{{ $n->size }}</small>@endif<i class="bi bi-box-arrow-up-right go"></i></a>
@endif
