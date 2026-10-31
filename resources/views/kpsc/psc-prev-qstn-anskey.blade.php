@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-" . $sub_category)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => $sub_category, 'msg' => 'എത്ര പഠിച്ചാലും മുൻവർഷ ചോദ്യങ്ങൾ ചെയ്ത് പരിശീലിക്കുക വിജയത്തിന് അനിവാര്യമാണ്. പുതിയ രീതി മനസ്സിലാക്കി പുതിയ ചോദ്യങ്ങൾ മനസ്സിലാക്കി പഠിക്കാൻ..!!',
            'crumbs' => ['Question papers' => url('kpsc/prev-qstn-ans'), $category => url('kpsc/prev-qstn-ans/' . Str::slug($category))]])

        <div class="ca-list">
            @forelse ($qstn_answer_key as $key => $list)
                <div class="ca-row" style="flex-wrap:wrap">
                    <span class="ca-ic"><i class="fa fa-file-pdf-o"></i></span>
                    <div class="t">{{ $list->title }}</div>
                    <div class="d-flex flex-wrap gap-2" style="gap:8px">
                        @if ($list->qstn_paper)<a class="ca-go" href="{{ Storage::url('files/' . $list->qstn_paper) }}" download="{{ $list->title }}"><i class="fa fa-download"></i> Question paper</a>@endif
                        @if ($list->ans_key)<a class="ca-go" style="background:#1d5fc4" href="{{ Storage::url('files/' . $list->ans_key) }}" download="{{ $list->title }}"><i class="fa fa-key"></i> Answer key</a>@endif
                    </div>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty">No question papers added here yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
