@extends('layouts.kpsc-app')
@extends('kpsc.section_header')

@section('title', "KPSC - New Pattern Question And Answer")
@section('content')
<div class="page-content-wrapper">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
      <div class="container">
        <h6 class="mb-3 newsten-title text-capitalize">Psc New Pattern Question And Answer</h6>
      </div>
      <div class="container">
        <!-- Default Accordian-->
        <div class="accordion" id="accordionExample">
            @foreach($question_list as $key => $list)
                  <!-- Single Accordian-->
                  <div class="card">
                    <div class="card-header p-0" id="heading{{$list->id}}">
                        <h2 class="btn text-left mb-0" type="button">
                           {!! $list->question !!}
                           <br>
                           <a class="btn btn-info btn-sm mt-2" href="{{url('kpsc/psc-new-pattern/'.$list->id)}}">Try it</a>
                        </h2>
                        
                    </div>
                    
                  </div>
                  {!! ads_space_sport($key) !!}
            @endforeach
        </div>
      </div>
  
    </div>
  </div>
@endsection