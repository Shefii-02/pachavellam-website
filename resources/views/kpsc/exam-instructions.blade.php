@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@php $title = ($exam->examtitle ?: 'Exam') . ' - Instructions'; @endphp
@section('title', ($exam->examtitle ?: 'Exam') . ' - Instructions')
@section('content')
    <div class="page-content-wrapper"><div class="container py-3" style="max-width:820px">
        @include('kpsc.partials.exam-instructions', ['exam' => $exam, 'instruction' => $instruction, 'meta' => $meta, 'startUrl' => $startUrl ?? null, 'startsAt' => $startsAt ?? null])
        @if (!empty($lastResult))<p class="mt-3 text-center"><a href="{{ $lastResult }}">View your last result</a></p>@endif
    </div></div>
@endsection
