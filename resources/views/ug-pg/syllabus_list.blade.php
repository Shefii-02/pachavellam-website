@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Syllabus')
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">{{$title}}</h5>
        </div>
      </div>
      <div class="container">
       @foreach($list as $key => $listing)
          <!-- Single Trending Post-->
          {{--<div class="single-trending-post d-flex">
            <div class="post-content">
             <a class="post-title" href="{{ Storage::url('syllabus/'.$listing->file) }}" download="{{$listing->title}}">
                {{$listing->title}}
              </a>
              <div class="col-12 d-flex">
                <a class="badge badge-pill badge-primary p-2" href="{{ Storage::url('syllabus/'.$listing->file) }}" download="{{$listing->title}}">
                  Download <span class="fa fa-download"></span>
                </a>
                
              </div>
              </div>
            </div>--}}
            <div class="single-trending-post d-flex">
                <div class="post-content">
                    <a class="post-title" href="{{ug_pg('syllabus/'.$university.'/'.$listing->level_name.'/'.$listing->name_slug)}}">
                    {{$listing->title}}
                    </a>
                    <div class="col-12">
                        <a class=" float-right badge badge-pill badge-primary p-2" href="{{ug_pg('syllabus/'.$university.'/'.$listing->level_name.'/'.$listing->name_slug)}}" >
                          Open <span class="fa fa-arrow-right"></span>
                        </a>
                         <a class="mr-3 float-right badge badge-pill badge-primary p-2" href="{{ug_pg('syllabus/'.$university.'/'.$listing->level_name.'/'.$listing->name_slug.'/download')}}" >
                          Downlaod <span class="fa fa-download"></span>
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