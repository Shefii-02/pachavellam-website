@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Category')
@section('content')


<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Category</h5>
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
                                        <a href="{{ug_pg($section.'/course/'.$item->university_name.'/'.$item->name_slug)}}" class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3    text-uppercase  text-light h6">{{$item->level_name}}</h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                            
                            
                                @endforeach
                         
                            
                          
                                @include('ug-pg.bottom-request')
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </div>
@endsection