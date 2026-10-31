@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-".$month.'-'.$year)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
@php
    $withDay = $daily_ca_day->where('day', '<>', null);
    $files = $withDay->count() > 0 ? $daily_ca_day->where('day', '=', null) : $daily_ca_day;   // files of the month itself; the dated ones open a day
@endphp
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Daily Current Affairs ' . $month . ' ' . $year, 'msg' => 'വരാനിരിക്കുന്ന പരീക്ഷകൾക്കുള്ള ' . $year . ' ലെ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!',
            'crumbs' => ['Current Affairs' => url('kpsc/psc-daily-current-affairs'), $year => url('kpsc/psc-daily-current-affairs/' . $year)]])

        @if ($files->count())
            @if ($withDay->count())<h2 class="ca-sec">Files</h2>@endif
            <div class="ca-list">
                @foreach ($files as $key => $list)
                    @include('kpsc.partials.ca-file-row', ['list' => $list])
                    {!! ads_space_sport($key) !!}
                @endforeach
            </div>
        @endif

        @if ($withDay->count())
            <h2 class="ca-sec">Choose a day</h2>
            <div class="ca-grid">
                @foreach ($withDay as $key => $list)
                    <div class="ca-cell">
                        <a href="{{ url('kpsc/psc-daily-current-affairs/' . Str::slug($year) . '/' . Str::slug($month) . '/' . Str::slug($list->day)) }}" class="ca-tile">
                            <span class="ca-ic"><i class="fa fa-calendar-check-o"></i></span>
                            <span class="ca-tl">{{ ucfirst($list->day) }}</span>
                        </a>
                    </div>
                    {!! ads_space_sport($key) !!}
                @endforeach
            </div>
        @elseif (!$files->count())
            <div class="ca-empty">Nothing added for this month yet.</div>
        @endif
    </div>
</div>
@endsection
