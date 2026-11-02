{{-- One current affairs file: a PDF (downloads) or a video link. Needs $list. --}}
@php $pdf = $list->type == 'Pdf'; @endphp
<div class="ca-row">
    <span class="ca-ic {{ $pdf ? '' : 'vid' }}"><i class="fa {{ $pdf ? 'fa-file-pdf-o' : 'fa-video-camera' }}"></i></span>
    <div class="t">{!! $list->title !!}</div>
    <a class="ca-go" @if ($pdf) href="{{ pdf_view_url(Storage::url('files/' . $list->file_name), strip_tags($list->title)) }}" @else target="_blank" rel="noopener" href="{!! $list->file_name !!}" @endif>{{ $pdf ? 'Open' : 'Watch' }}</a>
</div>
