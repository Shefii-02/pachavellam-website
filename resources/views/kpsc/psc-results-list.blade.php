@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Result-".$results_list[0]->type )
@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">Result-{{$results_list[0]->type}} </h5>
        </div>
      </div>
      <div class="container">
        @foreach($results_list as $key => $list)
          <!-- Single Trending Post-->
          <div class="single-trending-post d-flex">
            <div class="post-content">
              <a class="post-title" href="{{ Storage::url('files/'.$list->file_name) }}" download="{{$list->title}}">
                {{$list->title}}
              </a>
              
            </div>
            <div class="post-thumbnail text-right">
              <a  href="{{ Storage::url('files/'.$list->file_name) }}" download="{{$list->title}}">
                <img src="{{url('psc/img/download-png.png')}}" alt="">
              </a>
            </div>
          </div>
                  {!! ads_space_sport($key) !!}
        @endforeach
      </div>
      
    </div>
  </div>
@endsection