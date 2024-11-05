@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-".$qstn_answer_key[0]->sub_category)
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">{{$qstn_answer_key[0]->sub_category}} </h5>
        </div>
      </div>
      <div class="container">
        @foreach($qstn_answer_key as $key => $list)
          <!-- Single Trending Post-->
          <div class="single-trending-post d-flex">
            <div class="post-content">
              <a class="post-title" href="{{ Storage::url($list->qstn_paper) }}" download="{{$list->title}}">
                {{$list->title}}
              </a>
              <div class="col-12 d-flex">
                <a class="badge badge-pill badge-primary p-2" href="{{ Storage::url($list->qstn_paper) }}" download="{{$list->title}}">
                  Question paper <span class="fa fa-download"></span>
                </a>
                &nbsp;&nbsp; &nbsp;&nbsp;
                <a class="badge badge-pill badge-success p-2" href="{{ Storage::url($list->ans_key) }}" download="{{$list->title}}">
                  Answer Key <span class="fa fa-download"></span>
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