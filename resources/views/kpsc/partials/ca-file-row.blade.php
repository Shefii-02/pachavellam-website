{{-- One current affairs file: a PDF (downloads) or a video link. Needs $list. --}}
@php $pdf = $list->type == 'Pdf'; @endphp
<div class="ca-row">
    <span class="ca-ic {{ $pdf ? '' : 'vid' }}"><i class="fa {{ $pdf ? 'fa-file-pdf-o' : 'fa-video-camera' }}"></i></span>
    <div class="t">{!! $list->title !!}</div>
    <a class="ca-go" target="_blank" rel="noopener" @if ($pdf) download="{!! $list->title !!}.pdf" href="{{ Storage::url('files/' . $list->file_name) }}" @else href="{!! $list->file_name !!}" @endif>{{ $pdf ? 'Open' : 'Watch' }}</a>
</div>
