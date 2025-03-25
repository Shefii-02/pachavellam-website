@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Material-List')
@section('content')


<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Study Materails</h5>
            </div>
        </div>
                
                        
                        <div class="container">
                            
                        <div class="container mt-3 row" >
                            @include('ug-pg.share-current-page')
                        </div>
                                    @foreach ($list as $item)
                                    
                                        <div class="single-trending-post d-flex">
                                            <div class="post-content">
                                             
                                              <span class="badge badge-pill badge-primary ">{{$item->type}}</span><br>
                                              <span class="text-dark"> {{$item->title}} </span><br>
                                            {{-- @if( || 
                                                  
                                                    <a class="float-right badge badge-pill badge-success" href="{{ Storage::url('ug-pg/material/'.$item->content) }}" target="_new">
                                                        Open
                                                    </a>
                                            --}}
                                                @if(($item->type == 'Text Type') || ($item->type == 'Pdf File') || ($item->type == 'Pdf Link'))
                                                    <a href="{{ug_pg('materials/'.$item->university_name.'/'.$item->level_name.'/'.$item->course_name.'/'.$item->semester_name.'/'.$item->subject_name.'/'.$item->name_slug)}}" class="btn-btn-info float-right badge badge-pill badge-success p-2">
                                                        Open
                                                    </a>
                                                @elseif(($item->type == 'Youtube Link') || ($item->type == 'Other Page Link'))
                                                
                                                    <a  class="float-right badge badge-pill badge-success p-2" href="{{$item->content}}" target="_new">Open</a>
                                                @else
                                                    -------  
                                                @endif 
                                            
                                            </div>
                                        </div>
                                    
                                    @endforeach
                                
                            @include('ug-pg.bottom-request')
                         
                            
                          
                          
                        </div>
                        
                   
    </div>
     {!! single_ads_show() !!}
  </div>
@endsection