@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Syllabus-" )
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'Syllabus', 'msg' => 'മത്സരപരീക്ഷകൾക്കുള്ള സിലബസുകൾ ഇവിടെ ലഭിക്കും.'])

        @if (count($syllabus_type) > 0)
            <h2 class="ca-sec">Choose a category</h2>
            <div class="ca-grid">
                @foreach ($syllabus_type as $key => $type)
                    <div class="ca-cell">
                        <a href="{{ url('kpsc/psc-syllabus/' . Str::slug($type->type)) }}" class="ca-tile">
                            <span class="ca-ic"><i class="fa fa-list-alt"></i></span>
                            <span class="ca-tl">{{ ucfirst($type->type) }}</span>
                        </a>
                    </div>
                    {!! ads_space_sport($key) !!}
                @endforeach
            </div>
        @else
            <div class="ca-empty">മത്സരപരീക്ഷകൾക്ക് ആവശ്യമായ മെറ്റീരിയലുകൾ, സിലബസുകൾ എന്നിവ ഇവിടെ ഉടൻ ലഭ്യമാകും.</div>
        @endif
        {!! bottom_single_ads() !!}
    </div>
</div>
@endsection
