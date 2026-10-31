@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Result-" )
@section('styles')
@include('kpsc.partials.ca-style')
@endsection
@section('content')
<div class="page-content-wrapper ca-page">
    <div class="container">
        @include('kpsc.partials.ca-hero', ['heading' => 'PSC Results', 'msg' => 'പിഎസ്സി റിസൾട്ടുകൾ ഇവിടെ ലഭിക്കും.'])

        <h2 class="ca-sec">Choose a category</h2>
        <div class="ca-grid">
            @forelse ($results_type as $key => $type)
                <div class="ca-cell">
                    <a href="{{ url('kpsc/psc-results/' . Str::slug($type->type)) }}" class="ca-tile">
                        <span class="ca-ic"><i class="fa fa-trophy"></i></span>
                        <span class="ca-tl">{{ ucfirst($type->type) }}</span>
                    </a>
                </div>
                {!! ads_space_sport($key) !!}
            @empty
                <div class="ca-empty w-100 mx-2">No results added yet.</div>
            @endforelse
        </div>
        {!! bottom_double_ads() !!}
    </div>
</div>
@endsection
