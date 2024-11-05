@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC")
@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">{{$title}}</h5>
          <p class="mb-3 line-height-1">{{count($free_classlist)}} Class's</p>
        </div>
      </div>
      <div class="container">
        @foreach ($free_classlist as $key => $list)
              <!-- Single Trending Post-->
          <div class="single-trending-post d-flex ">
            <div class="post-thumbnail">
              <img src="https://static.uacdn.net/thumbnail/course/v2/5C2A3960-19CC-4654-97CA-4EE07DB12D4E_plus.png" alt="">
            </div>
            <div class="post-content"><a class="post-title" href="{{$list->link}}" target="_new">{{$list->title}}</a>
              <div class="post-meta d-flex align-items-center">
                <a href="{{$list->link}}" target="_new">
                  <i class="fa fa-eye"></i>  @if($list->view_count == 0) {{rand(1,5)}}k @else {{ $list->view_count}} @endif
                </i>
              </a>
              </div>
            </div>
          </div>
           {!! ads_space_sport($key) !!}
        @endforeach
        
       
      </div>
   
    </div>
  </div>
@endsection