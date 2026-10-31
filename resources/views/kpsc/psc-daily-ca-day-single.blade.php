@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-".$month.'-'.$day.'-'.$year)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => $month . ' ' . $day . ' ' . $year, 'msg' => 'വരാനിരിക്കുന്ന പരീക്ഷകൾക്കുള്ള ' . $month . ' ' . $day . ' ലെ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങൾ',
            'crumbs' => ['Current Affairs' => url('kpsc/psc-daily-current-affairs'), $year => url('kpsc/psc-daily-current-affairs/' . $year), $month => url('kpsc/psc-daily-current-affairs/' . $year . '/' . $month)]])

        <div class="ca-list">
            @forelse ($daily_ca_day_single as $key => $list)
                <div class="ca-q">
                    <span class="qn">Question {{ $key + 1 }}</span>
                    <div class="qt">{!! $list->question !!}</div>
                    <details>
                        <summary>Show answer</summary>
                        <div class="ca-ans"><b>{!! $list->answer !!}</b></div>
                        @if (trim(strip_tags((string) $list->note)) !== '')
                            <div class="ca-note"><strong>Notes:</strong> {!! $list->note !!}</div>
                        @endif
                    </details>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty">No questions for this day yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
