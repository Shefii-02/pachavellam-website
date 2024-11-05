@extends('layouts.ug-pg-app')
@section('title', $title ?? '- Latest Notifcations')
@extends('ug-pg.section_header')

@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title"><i class="lni lni-alarm"></i> Notifications </h5>
        </div>
      </div>
      <div class="container">
        @foreach ($noftify as $key => $notification_list)
            <!-- Single Trending Post-->
            <div class="single-trending-post d-flex">
            <div class="post-content">
                <a class="post-title" href="{{$notification_list->redirection}}">
                    {{$notification_list->title}} 
                </a>
                <div class="post-meta d-flex align-items-center"></div>
            </div>
            </div>
                 {!! ads_space_sport($key) !!}
        @endforeach
        
      </div>
      {!! single_ads_show() !!}
      {{-- <div class="container">
        <div class="text-center mt-3"><a class="btn btn-primary" href="#">Load More</a></div>
      </div> --}}
    </div>
  </div>
@endsection