@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Current Affairs-".$month.'-'.$day.'-'.$year)
@section('content')
<div class="page-content-wrapper">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
      <div class="container">
        <h6 class="mb-3 newsten-title text-capitalize">{{$month}} {{$day}} {{$year}}</h6>
      </div>
      <div class="container">
        <!-- Default Accordian-->
        <div class="accordion" id="accordionExample">
          
            @foreach($daily_ca_day_single as $key => $list)
                  <!-- Single Accordian-->
                  <div class="card">
                    <div class="card-header p-0" id="heading{{$list->id}}" data-toggle="collapse" data-target="#collapse{{$list->id}}" aria-expanded="false" aria-controls="collapse{{$list->id}}">
                        <h2 class="btn text-left mb-0" type="button"  >
                           {!! $list->question !!}
                           
                        </h2><br>
                      <a class="btn btn-info btn-sm mt-2 ml-3 text-light">Show answer</a>
                    </div>
                    <div class="collapse " id="collapse{{$list->id}}" aria-labelledby="headingOne" data-parent="#accordionExample">
                      <div class="card-body">
                        <strong class="mb-0">
                            {!! $list->answer !!}
                         </strong>    
                            <br>
                            <small>Notes :-</small>
                            <p>{!! $list->note  !!}</p>
                       
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