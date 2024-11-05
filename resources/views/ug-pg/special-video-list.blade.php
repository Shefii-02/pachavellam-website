@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? 'Sepecial-Class-List')
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
</style>
@endsection
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">Special Videos </h5>
          <p class="mb-3 line-height-1">{{count($spcl_video)}} Class's</p>
        </div>
      </div>

      @foreach($spcl_video->unique('category') as $key => $categ)
            <div class="col-12 text-center">
                    <h5><u>{{$categ->category}}</u></h5>
            </div>
            <div class="container">
                <div class="row">
                        
                    @foreach ($spcl_video->where('category',$categ->category) as $videos)
                        <!-- Single Trending Post-->
                        <div class="col-md-3 col-6 ">
                            <div class="card specialvideos">
                            <a class=" w-100" href="https://youtu.be/{{$videos->redirection}}">
                                <img src="https://img.youtube.com/vi/{{$videos->redirection}}/0.jpg" alt="">
                            <h6 class="text_limit">{{$videos->title}}</h6></a></div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
       
   
    </div>
  </div>
@endsection