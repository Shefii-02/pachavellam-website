@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Question-Answers")
@section('content')

@section('styles')
<style>
    .social-share a {
            padding: 0 .2em;
        }
    .linked-list{
        color: #0e76a8;
    }
    .twitter{
        color: #00acee;
    }
    .bg1{
        background-image: linear-gradient(to top, #4481eb 0%, #04befe 100%);
    }
    .bg2{
        background-image: linear-gradient(to right, #3ab5b0 0%, #3d99be 31%, #56317a 100%);
    }
    .bg3{
        background-image: linear-gradient(-20deg, #b721ff 0%, #21d4fd 100%);
    }
</style>
@endsection
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">{{$title}}</h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                            
                            @foreach ($prev_type as $list)
                                <div class="col-lg-4">
                                    <a href="{{url('kpsc/prev-qstn-ans/'.Str::slug($category).'/'.Str::slug($list->subcategory))}}" class="btn-btn-info text-light">
                                        <div class="card text-white  bg1 mb-3" style="max-width: 100%;">
                                        <div class="card-header">{{$list->subcategory}}</div>
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-light">
                                                <a class="text-light" href="{{url('kpsc/prev-qstn-ans/'.Str::slug($category).'/'.Str::slug($list->subcategory))}}">
                                                   <u >open</u>
                                                </a>
                                            </h5>
                                        </div>
                                        </div>
                                    </a>
                                </div>  
                            @endforeach
                           
                            
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </div>
@endsection