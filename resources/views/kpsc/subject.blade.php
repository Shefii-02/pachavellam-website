@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "Kerala PSC-")
@section('styles')
    <style>
        .w-img-25{
            width:25%;
        }
        
        @media only screen and (max-width: 600px) {
            .w-img-25{
                width:50%;
            }
        }
    </style>
@endsection

@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Kerala Psc  </h5>
            </div>
        </div>
        <div class="container">
            
            <div class="row">
                
           
                @foreach($kpsc_activity as $list)
                 <!-- Single Trending Post-->
                    <div class="col-lg-3 col-md-3 col-6 mb-2" > 
                    @php
                                        
                            if(!isset($sub_category)){
                                $url = url('kpsc/subject/'.$category_name.'/'.$list->slug_name);
                            }
                            else{
                                $url = url('kpsc/subject/'.$category_name.'/'.$sub_category.'/'.$list->slug_name);
                            }
                            
                            @endphp
                        @if($kpsc_subject_activity->where('id',$list->id)->count() <= 0)
                        
                        @else
                        
                            <a href="{{$url}}">
                        @endif
                        
                        <div  class="rounded text-dark position-relative" style="background:{{$list->bg_color}}">
                            @if($kpsc_subject_activity->where('id',$list->id)->count() <= 0)
                                <div class=" h-100 position-absolute text-center w-100" style="background-color: #9b9999b0;cursor: none;">
                                        <p style="color: #fff;font-weight: 800;" class="m-5">Unavailable now</p>
                                </div>
                            @endif
                           
                                <div class=" p-2">
                                      <div class="post-thumbnail1 text-center"> 
                                        <img src="{{$list->image}}" alt="" class="w-img-25">
                                      </div> 
                                      <div class="post-content1 p-3 text-center">
                                            
                                        <p class="post-title1" style="font-size:1rem; color:{{$list->text_color}}">  
                                        {{$list->activity}}
                                        </p>
                                      </div>
                                </div>
                        </div>
                       @if($kpsc_subject_activity->where('id',$list->id)->count() <= 0)
                        
                        @else
                        
                            </a>
                        @endif
                    </div>
                @endforeach
                
                
                   
                 
            
            </div>
        </div>
    </div>
  </div>
@endsection