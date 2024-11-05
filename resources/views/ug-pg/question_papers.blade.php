@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Previous Question-Papers')
@section('content')


<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Question Papper</h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                            @include('ug-pg.share-current-page')
                            <div class="col-lg-12">
                                <div class="row">
                                    @foreach ($list as $item)
                                    
                                        <div class="col-md-4 col-6 h-25 text-center">
                                            <a href="{{ug_pg($section.'/'.$item->university_name.'/'.$item->level_name.'/'.$item->course_name.'/'.$item->semester_name.'/'.$item->subject_name.'/'.$item->name_slug)}}" class="btn-btn-info text-light">
                                            <img src="/sample-image/images-2.png" width="100px">
                                            <h1 class="h6 mt-1  text-capitalize">{{$item->title}}</h1>
                                             </a>    
                                        </div>

                                    @endforeach
                                </div>
                            </div>
                            @include('ug-pg.bottom-request')
                         
                            
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </div>
@endsection