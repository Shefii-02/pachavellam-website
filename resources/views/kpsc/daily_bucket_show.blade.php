@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC")
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-center mb-3">
        <img src="/images/roaring.png" class="rounded" alt="daily-fact">
          <!--<h5 class="mb-3 newsten-title"> {{$title}} </h5>-->
        </div>
      </div>
      
      <div class="container">
        <div class="container mt-3 row" >
            @include('ug-pg.share-current-page')
              {!! single_ads_show() !!}
        </div>
        @foreach($dailyBucket_list as $key => $list)
          <!-- Single Trending Post-->
            <div class="single-trending-post d-flex">
                <div class="post-content">
                 
                  <span class="badge badge-pill badge-primary ">{{$list->type}}</span><br>
                  <span class="text-dark"> {{$list->title}} </span><br>
                    @if($list->type == 'Pdf')
                      
                        <a class="float-right badge badge-pill badge-success p-4" href="{{url('kpsc/daily-bucket/pdf/'.$list->class_date.'/'.$list->content)}}" target="_new">
                            Open/Download
                        </a>
                        
                        
                    @elseif($list->type == 'Voice Msg')
                    
                        <audio controls>
                            
                          <source src="{{ Storage::url('daily-buckets/'.$list->content) }}" type="audio/ogg">
                          <source src="{{ Storage::url('daily-buckets/'.$list->content) }}" type="audio/mpeg">
                        
                        </audio>
                    
                    @elseif($list->type == 'Text Msg')
                      {!! $list->content !!}
                    @elseif($list->type == 'Link')
                    
                        <a  class="float-right badge badge-pill badge-success p-2" href="{{$list->content}}" target="_new">Open</a>
                    @else
                        -------
                    @endif
                </div>
            </div>
             {!! ads_space_sport($key) !!}
        @endforeach    
          </div>
      </div>
      {!! single_ads_show() !!}
    <!--</div>-->
  </div>
@endsection