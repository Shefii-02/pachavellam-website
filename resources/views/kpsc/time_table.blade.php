@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Time Tables-" )
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title"> Time Tables </h5>
        </div>
      </div>
      <div class="container">
        @foreach($time_table as $key => $list)
          <!-- Single Trending Post-->
          <div class="single-trending-post d-flex">
            <div class="post-content">
              <a class="post-title" href="{{ pdf_view_url(Storage::url('time-table/'.$list->file), $list->title) }}">
                {{$list->title}}
              </a>
              <div class="col-12 d-flex">
                <a class="badge badge-pill badge-primary p-2" href="{{ pdf_view_url(Storage::url('time-table/'.$list->file), $list->title) }}">
                  View <span class="fa fa-eye"></span>
                </a>
                
              </div>
              </div>
            </div>
             {!! ads_space_sport($key) !!}
        @endforeach    
          </div>
      </div>
      
    </div>
  </div>
@endsection