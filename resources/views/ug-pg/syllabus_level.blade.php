@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Syllabus-Category')
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
                <h5 class="mb-3 newsten-title">Syllabus Category</h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                           
                         
                              @include('ug-pg.share-current-page')

                                @foreach ($list as $item)
                                    
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a href="{{ug_pg('syllabus/'.$university.'/'.$item->level_name)}}" class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">{{$item->level_name}}</h3>
                                            
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