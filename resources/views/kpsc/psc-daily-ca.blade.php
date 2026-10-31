@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-")
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Daily Current Affairs', 'msg' => 'വരാനിരിക്കുന്ന പരീക്ഷകൾക്കുള്ള എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!'])

        <h2 class="ca-sec">Choose a year</h2>
        <div class="ca-grid">
            @forelse ($daily_ca_year as $key => $list)
                <div class="ca-cell">
                    <a href="{{ url('/kpsc/psc-daily-current-affairs/' . Str::slug($list->year)) }}" class="ca-tile">
                        <span class="ca-ic"><i class="fa fa-calendar"></i></span>
                        <span class="ca-tl">{{ $list->year }}</span>
                    </a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty w-100 mx-2">No current affairs yet.</div>
            @endforelse
        </div>
        {!! bottom_double_ads() !!}
    </div>
</div>
@endsection
