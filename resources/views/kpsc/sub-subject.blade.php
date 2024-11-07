@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('styles')
<style>
    div.parent {
      position: relative;
      width: 100%;
      height: 15vh;
      border: 1px solid black;
      border-radius:9px;
      overflow: hidden;
      background-color:#000;
      margin-bottom:10px;
    } 
    .child img{
        border-radius:9px;
    }
    div.child {
      position: absolute;
      bottom:-3px;
      right: -8px;
      width: 50%;
      height: 50%;
      box-shadow: 0 2px 4px 0 rgb(0 0 0 / 20%);
      transform: rotate(20deg);
    }
    p.text-child{
      color:#fff;
     padding:5px;
      top:0px;
      font-size:19px;
      font-weight:800;
      position: absolute;
      z-index:9999;
    }
    @media only screen and (min-width: 600px) {
        div.parent {
            
          height: 20vh !important;
        }
    }
    </style>
    @endsection
    
@section('title', $title ?? "KPSC-".$category_name)
@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title text-capitalize">{{$category_name}}  Subjects </h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                @foreach ($subcategory_check as $list)
                <!-- Catagory Card-->
                
                <div class="col-lg-3 col-sm-3 col-sm-6 col-6">
                        <a href="{{url('kpsc/subject/'.$category_name.'/'.$list->slug_name)}}">
                    <div class="parent ">
                            <div class="child">
                              <img width="100%"  src="{{ Storage::url('files/'.$list->image) }}">
                            </div>
                            <p class="text-child">{{$list->subject_title}}</p>
                    </div>
                        </a>
                </div>
                @endforeach
                
                 {!! bottom_double_ads() !!}
                 
            </div>
        </div>
    </div>
  </div>
@endsection