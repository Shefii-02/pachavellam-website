@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@php $type = $results_list->first()->type ?? ''; @endphp
@section('title', $title ?? "KPSC-Result-" . $type)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Result' . ($type ? ' - ' . $type : ''), 'msg' => 'പിഎസ്സി റിസൾട്ടുകൾ ഇവിടെ ലഭിക്കും.',
            'crumbs' => ['Results' => url('kpsc/psc-results')]])

        <div class="ca-list">
            @forelse ($results_list as $key => $list)
                <div class="ca-row">
                    <span class="ca-ic"><i class="fa fa-file-pdf-o"></i></span>
                    <div class="t">{{ $list->title }}</div>
                    <a class="ca-go" href="{{ Storage::url('files/' . $list->file_name) }}" download="{{ $list->title }}"><i class="fa fa-download"></i> Download</a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty">No results added in this category yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
