@extends('layouts.ug-pg-app')
@section('title', $title ?? 'University-News')
@extends('ug-pg.section_header')

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
 p img{
        width:100% !important;
        height: auto !important;
    }
</style>
@endsection
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title"></h5>
        </div>
      </div>

      
            <div class="container">
                <div class="row">
                    <div class="card col-lg-12">
                        <div class="rounded mx-auto d-block">
                            
                        </div>
                        <h6 class="card-header text-capitalize">{{$news_list->title}}</h6>
                        <p class="card-body news-content">{!! $news_list->description !!}</p>
                    </div>
                   
                </div>
            </div>
       
    </div>
  </div>
@endsection