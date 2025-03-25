@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-News-List')
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
          <h5 class="mb-3 newsten-title">Latest News </h5>
          <p class="mb-3 line-height-1">{{count($news_list)}} News</p>
        </div>
      </div>

      
            <div class="container">
                <div class="row">
                        
                    @foreach ($news_list as $news)
                        <!-- Single Trending Post-->
                        <div class="col-md-4 col-12 ">
                            <div class="card specialvideos">
                            <a class=" w-100" href="{{ug_pg('news/'.$news->id)}}">
                                <img src="{{ Storage::url('files/'.$news->image) }}" alt="">
                            <h6 class="text_limit">{{$news->title}}</h6></a></div>
                        </div>
                    @endforeach
                </div>
            </div>
       
      {{-- <div class="container">
        <div class="text-center mt-3"><a class="btn btn-primary" href="#">Load More</a></div>
      </div> --}}
    </div>
  </div>
@endsection