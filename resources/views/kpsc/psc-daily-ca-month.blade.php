@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-".$year)
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Daily Current Affairs ' . $year, 'msg' => 'വരാനിരിക്കുന്ന പരീക്ഷകൾക്കുള്ള ' . $year . ' ലെ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!',
            'crumbs' => ['Current Affairs' => url('kpsc/psc-daily-current-affairs')]])

        <h2 class="ca-sec">Choose a month</h2>
        <div class="ca-grid">
            @forelse ($daily_ca_month->sortBy('month_number') as $key => $list)
                <div class="ca-cell">
                    <a href="{{ url('/kpsc/psc-daily-current-affairs/' . Str::slug($year) . '/' . Str::slug($list->month)) }}" class="ca-tile">
                        <span class="ca-ic"><i class="fa fa-calendar-o"></i></span>
                        <span class="ca-tl">{{ $list->month }}</span>
                    </a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty w-100 mx-2">No current affairs for {{ $year }} yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
