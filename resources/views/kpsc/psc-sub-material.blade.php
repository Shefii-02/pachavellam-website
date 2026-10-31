@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@php $type = $material_list->first()->type ?? ''; @endphp
@section('title', $title ?? "KPSC-Materials-" . $type)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Study Material' . ($type ? ' - ' . $type : ''), 'msg' => 'മത്സരപരീക്ഷകൾക്കുള്ള സ്റ്റഡി മെറ്റീരിയലുകൾ ഇവിടെ ലഭിക്കും.',
            'crumbs' => ['Study material' => url('kpsc/psc-material')]])

        <div class="ca-list">
            @forelse ($material_list as $key => $list)
                <div class="ca-row">
                    <span class="ca-ic"><i class="fa fa-file-pdf-o"></i></span>
                    <div class="t">
                        {{ $list->title }}
                        @if ($list->date)<div class="small text-muted fw-normal"><i class="fa fa-calendar"></i> {{ $list->date }}</div>@endif
                    </div>
                    <a class="ca-go" href="{{ Storage::url('files/' . $list->file_name) }}" download="{{ $list->title }}"><i class="fa fa-download"></i> Download</a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty">No study material added in this category yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
