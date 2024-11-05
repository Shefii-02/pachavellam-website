@extends('layouts.kpsc-app')
@extends('kpsc.section_header')

@section('title', $title ?? "KPSC-".$category_name)
@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">{{$category_name}}  Subjects </h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                @foreach ($subcategory_check as $list)
                <!-- Catagory Card-->
                <div class="col-lg-3 col-sm-3 col-sm-6 col-6  catagory-card">
                <a href="{{url('kpsc/free/sub/'.$list->name_slug)}}">
                    <img src="{{ Storage::url($list->image) }}" alt="{{$list->subcategory_name}}" class="w-100">
                    <h6>{{$list->subcategory_name}}</h6>
                </a>
                </div>
                @endforeach
                
                 {!! bottom_double_ads() !!}
                 
            </div>
        </div>
    </div>
  </div>
@endsection