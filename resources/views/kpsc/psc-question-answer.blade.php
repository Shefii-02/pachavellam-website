@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Question-Answer")
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Previous Question Papers & Answer Keys', 'msg' => 'എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!'])

        <h2 class="ca-sec">Choose a category</h2>
        <div class="ca-grid">
            @forelse ($prev_type as $key => $list)
                <div class="ca-cell">
                    <a href="{{ url('kpsc/prev-qstn-ans/' . Str::slug($list->category)) }}" class="ca-tile">
                        <span class="ca-ic"><i class="fa fa-folder-open-o"></i></span>
                        <span class="ca-tl">{{ $list->category }}</span>
                    </a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty w-100 mx-2">No question papers yet.</div>
            @endforelse
        </div>
        {!! bottom_double_ads() !!}
    </div>
</div>
@endsection
