@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Material-")
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'PSC Study Materials', 'msg' => 'മത്സരപരീക്ഷകൾക്കുള്ള സ്റ്റഡി മെറ്റീരിയലുകൾ ഇവിടെ ലഭിക്കും.'])

        @if (count($material_type) > 0)
            <h2 class="ca-sec">Choose a category</h2>
            <div class="ca-grid">
                @foreach ($material_type as $key => $list)
                    <div class="ca-cell">
                        <a href="{{ url('kpsc/psc-material/' . Str::slug($list->type)) }}" class="ca-tile">
                            <span class="ca-ic"><i class="fa fa-book"></i></span>
                            <span class="ca-tl">{{ $list->type }}</span>
                        </a>
                    </div>
                    {!! ads_space_sport($key) !!}
                @endforeach
            </div>
        @else
            <div class="ca-empty">മത്സരപരീക്ഷകൾക്ക് ആവശ്യമായ മെറ്റീരിയലുകൾ, സിലബസുകൾ എന്നിവ ഇവിടെ ഉടൻ ലഭ്യമാകും.</div>
        @endif
    </div>
</div>
@endsection
