@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@php $type = $syllabus_list->first()->type ?? ''; @endphp
@section('title', $title ?? "KPSC-Syllabus-" . $type)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Syllabus ' . ($type ? '- ' . $type : ''), 'msg' => 'മത്സരപരീക്ഷകൾക്കുള്ള സിലബസുകൾ ഇവിടെ ലഭിക്കും.',
            'crumbs' => ['Syllabus' => url('kpsc/psc-syllabus')]])

        <div class="ca-list">
            @forelse ($syllabus_list as $key => $list)
                @php $file = Storage::url('files/' . $list->file_name); @endphp
                <div class="ca-row">
                    <span class="ca-ic"><i class="fa fa-file-pdf-o"></i></span>
                    <div class="t">
                        {{ $list->title }}
                        @if ($list->category_no)<div class="small text-muted fw-normal">Category No: {{ $list->category_no }}</div>@endif
                    </div>
                    <a class="ca-go" href="{{ $file }}" download="{{ $list->title }}"><i class="fa fa-download"></i> Download</a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty">No syllabus added in this category yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
