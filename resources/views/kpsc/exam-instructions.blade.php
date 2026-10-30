@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@php $title = ($exam->examtitle ?: 'Exam') . ' - Instructions'; @endphp
@section('title', ($exam->examtitle ?: 'Exam') . ' - Instructions')
@section('content')
    <div class="page-content-wrapper"><div class="container py-3" style="max-width:820px">
        @include('kpsc.partials.exam-instructions', ['exam' => $exam, 'instruction' => $instruction, 'meta' => $meta, 'startUrl' => $startUrl ?? null, 'closeUrl' => $closeUrl ?? null, 'startsAt' => $startsAt ?? null])
        @include('partials.ad-slot', ['type' => 'web_banner', 'page' => 'exam_intro', 'compact' => true])
        @if (!empty($summary))@include('kpsc.partials.exam-summary', ['summary' => $summary])@endif
        @if (!empty($lastResult))<p class="mt-3 text-center"><a href="{{ $lastResult }}">View your last result</a></p>@endif
    </div></div>
@endsection
