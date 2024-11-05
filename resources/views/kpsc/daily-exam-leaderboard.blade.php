@extends('layouts.without-bottom')
  @php $title="Leader Board" @endphp
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Daily Exam Discussion-" )


@section('content')

  @section('styles')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Josefin+Sans&display=swap');
    body{
      background: linear-gradient(to bottom right, #38A3A5, #57CC99, #80ED99);
    }
    
    .card{
      min-height: 100vh;
      width: 100%;
      margin-top: 5rem;
          padding-top: 3rem;
      padding-bottom: 2rem;
      border-radius: 15px;
      background: #02383C;
    }
    
    
    ::selection{
      background: rgba(210, 255, 213, 0.3);
    }
    
    .photo{
      width: 75px;
      background: #fff;
      border-radius: 50%;
      border: 5px solid cyan;
      box-shadow: 0 0 20px cyan;
      margin: 1rem 0;
    }
    
    .main{
      width: 85px;
    }
    
    .profile{
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 1rem;
    }
    
    .profile .person{
      display: flex;
  
        margin-right: 1.7rem;
        margin-bottom: 1rem;
        margin-left: 2.5rem;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }
    
    .profile .person.first{
      z-index: 10;
      transform: translateY(-33%);
    }
    
    .first .fa-crown{
      color: gold;
      filter: drop-shadow(0px 0px 5px gold);
    }
    
    .num{
      color: white;
    }
    p {
        margin:0;
    }
    .fa-caret-up,.fa-caret-down{
      color: cyan;
      font-size: 21px;
    }
    
    .link{
      margin: 0.2rem 0;
      color: #fff;
      margin-top: -0.3rem;
      font-size: 13px;
    }
    
    .points{
      color: cyan;
      font-size: 17px;
    }
    
    .second{
      margin-right: -0.7rem !important;
    }
    
    .third{
        margin-top:5.7rem;
      margin-left: -0.7rem !important;
    }
    
    .p_img{
      width: 50px;
      background: #fff;
      border-radius: 50%;
    }
    
    .flex{
      display: flex;
      align-items: center;
    }
    
    .others{
      display: flex;
      width: 100%;
      margin-top: 1rem;
      align-items: center;
      justify-content: center;
    }
    
    .info{
      width:100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 30px;
      background: rgba(210, 255, 213, 0.3);
    }
    
    .info .points{
      margin-left: 0.2rem;
      margin-right: 1.2rem;
    }
    
    .info .link{
      margin: 0 1rem;
    }
    
    .rank{
      display: flex;
      align-items: center;
      margin: 0 1rem;
      flex-direction: column-reverse;
    }
    
    .rank i{
      margin-top: -5px !important;
    }
    
    .rank .num{
      margin: 0 !important; 
    }
</style>
@endsection


 <div class="card one col-sm-12">

  <div class="profile">
      @if($list_attempt->get(1) != null)
    <div class="person second">
      <div class="num">2</div>
      <i class="fas fa-caret-up"></i>
      <img src="@if($list_attempt->get(1)->image != null) {{ Storage::url('users/'.$list_attempt->get(1)->image) }} @else https://img.icons8.com/bubbles/100/000000/user.png @endif" alt="" class="photo">
      <p class="link">@ {!! $list_attempt->get(1)->name !!}</p>
      <p class="points">{!! number_format($list_attempt->get(1)->total_mark,2) !!}</p>
      <p class="points">{!! date('H:i:s',$list_attempt->get(1)->attempt_time) !!}</p>
    </div>
    @endif
    @if($list_attempt->get(0) != null)
    <div class="person first">
      <div class="num">1</div>
      <i class="fas fa-crown"></i>
      <img src="@if($list_attempt->get(0)->image) {{ Storage::url('users/'.$list_attempt->get(0)->image) }} @else https://img.icons8.com/bubbles/100/000000/user.png @endif" alt="" class="photo main">
      <p class="link">@ {!! $list_attempt->get(0)->name !!}</p>
      <p class="points">{!! number_format($list_attempt->get(0)->total_mark,2) !!}</p>
      <p class="points">{!! date('H:i:s',$list_attempt->get(0)->attempt_time) !!}</p>
    </div>
    @endif
    @if($list_attempt->get(2) != null)
    <div class="person third">
      <div class="num">3</div>
      <i class="fas fa-caret-up"></i>
      
      <img src="@if($list_attempt->get(2)->image != null) {{ Storage::url('users/'.$list_attempt->get(2)->image) }} @else https://img.icons8.com/bubbles/100/000000/user.png @endif" alt="" class="photo">
      <p class="link">@ {!! $list_attempt->get(2)->name !!}</p>
      <p class="points">{!! number_format($list_attempt->get(2)->total_mark,2) !!}</p>
      <p class="points">{!! date('H:i:s',$list_attempt->get(2)->attempt_time) !!}</p>
    </div>
    @endif
  </div>
  @if($list_attempt->slice(3)->count() >0)
      @foreach($list_attempt->slice(3) as $key => $list)
        <div class="rest">
            <div class="others flex">
              <div class="rank">
                <i class="fas fa-caret-down"></i>
                <p class="num">{{$key+1}}</p>
              </div>
              <div class="info flex">
                  
                <img src="@if($list->image != null) {{ Storage::url('users/'.$list->image) }} @else https://img.icons8.com/bubbles/100/000000/user.png @endif" alt="" class="p_img">
                <p class="link">@ {!! $list->name !!}</p>
                <p class="points">{!! number_format($list->total_mark,2) !!}<br>{!! date('H:i:s',$list->attempt_time) !!}</p>>
              </div>
            </div>
        </div>
      @endforeach
  @endif
</div>  
  
  @endsection