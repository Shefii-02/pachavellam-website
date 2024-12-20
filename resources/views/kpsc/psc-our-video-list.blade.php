@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Our videos-")
@section('content')

@section('styles')
<style>
    .specialvideos img{
        border-radius: 9px;
    }

    .specialvideos a h6 {
        -webkit-transition-duration: 500ms;
        -o-transition-duration: 500ms;
        transition-duration: 500ms;
        font-size: 12px;
        margin-bottom: 0;
        position: absolute;
        left: 0.5rem;
        bottom: 0.5rem;
        z-index: 100;
        color: #ffffff;
        background-color: #19af64d1;
        padding: 0.25rem 0.625rem;
        border-radius: 2rem;
    }

    .specialvideos h6 {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
    }
</style>
@endsection
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">{{$title}} </h5>
          <p class="mb-3 line-height-1">{{count($our_video)}} Class's</p>
        </div>
      </div>

      
            <div class="container">
                <div class="row">
                        
                    @foreach ($our_video as $key => $videos)
                        <!-- Single Trending Post-->
                        <div class="col-md-3 col-6 ">
                            <div class="card specialvideos">
                            <a class=" w-100" href="https://youtu.be/{{$videos->redirection}}">
                                <img src="https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg" alt="">
                            <h6 class="text_limit">{{$videos->title}}</h6></a></div>
                        </div>
                         {!! ads_space_sport($key) !!}
    
                    @endforeach
                </div>
            </div>
       
      {{-- <div class="container">
        <div class="text-center mt-3"><a class="btn btn-primary" href="#">Load More</a></div>
      </div> --}}
    </div>
    {!! bottom_single_ads() !!}}
  </div>
@endsection