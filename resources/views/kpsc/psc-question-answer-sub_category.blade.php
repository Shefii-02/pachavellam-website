@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Question-Answers")
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => ucwords($category), 'msg' => 'എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!',
            'crumbs' => ['Question papers' => url('kpsc/prev-qstn-ans')]])

        <h2 class="ca-sec">Choose a section</h2>
        <div class="ca-grid">
            @forelse ($prev_type as $key => $list)
                <div class="ca-cell">
                    <a href="{{ url('kpsc/prev-qstn-ans/' . Str::slug($category) . '/' . Str::slug($list->subcategory)) }}" class="ca-tile">
                        <span class="ca-ic"><i class="fa fa-file-text-o"></i></span>
                        <span class="ca-tl">{{ $list->subcategory }}</span>
                    </a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty w-100 mx-2">Nothing added in this category yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
