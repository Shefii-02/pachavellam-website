@extends('layouts.kpsc-app')
@extends('kpsc.section_header')

@section('title', $title ?? "KPSC Daily Buckets")
@section('content')
<div class="page-content-wrapper">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-center mb-3">
        <img src="/images/roaring.png" class="rounded" alt="daily-fact">
          <!--<h5 class="mb-3 newsten-title"> {{$title}} </h5>-->
        </div>
      </div>
      
                       
      <div class="container"> 
                        <div class="container mt-3 row" >
                            @include('ug-pg.share-current-page')
                             
                        </div>
        <!-- Default Accordian-->
        <div class="accordion" id="accordionExample">
            @foreach($dailyBucket_list->unique('day_title') as $key => $list)
                  <!-- Single Accordian-->
                  <div class="card">
                    <div class="card-header p-0" id="heading{{$list->id}}">
                        <h2 class="btn text-left mb-0" type="button">
                           {!! $list->day_title.'  ['.date('d-M-Y',strtotime($list->class_date)).']' ?? date('d-M-Y',strtotime($list->class_date)) !!}
                        </h2>
                         <a class="btn btn-info float-right btn-sm mt-2" href="{{url('kpsc/daily-bucket/'.$list->id)}}">Open</a>
                    </div>
                    
                  </div>
                  {!! ads_space_sport($key) !!}
            @endforeach
        </div>
      </div>
   {!! single_ads_show() !!}
    </div>
  </div>
@endsection