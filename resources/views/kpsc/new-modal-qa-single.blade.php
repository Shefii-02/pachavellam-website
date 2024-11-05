@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', "KPSC - New Pattern Question And Answer")
@section('content')
@section('styles')
    <style>
            .question-bar ul{
            margin: 0;
            list-style: none;
            color: #7c79c7;
            border: 1px solid #efeeff;
            padding: 20px;
            border-radius: 10px;
            background: #fcfcff;
            }
            .question-bar  ul li+li{
            margin-top: 16px;
            border-top: 1px solid #efeeff;
            padding-top: 16px;
            }
            .question-bar  ul li input[type="radio"] {
            display: none;
            }
            .question-bar  ul li input[type="radio"]+label{
            position:relative;
            padding-left: 25px;
            
            color: #000;
            
            }
            .question-bar  ul li input[type="radio"]+label::before{
            content:"";
            position: absolute;
            left: -7px;
            background: #ffffff;
            height: 25px;
            width: 25px;
            border-radius: 50%;
            top: 0px;
            border: 1px solid #7c79c7;
            } 
            
            .question-bar  ul li input[type="radio"]:checked+label::before {
            content: "";
            background: #0b03fc;
            border-color: #0b03fc;
            }
            
            .question-bar  ul li input[type="radio"]:checked+label::after {
            content: "";
            position: absolute;
            left: 6px;
            top: 7px;
            height: 7px;
            width: 7px;
            background: #fff;
            border-radius: 50%;
            }
            
            .score-color--white{
            background: #fff;
            }
            h2{
            font-size: 14px;
            margin: 0;
            padding-bottom: 30px;
            color: #000;
            line-height: 2;
            font-weight: bold;
            }
            
            .quize{
            max-width: 700px;
            width: 100%;
            margin: 50px auto;
            padding: 50px;
            background: #ffffff;
            border-radius: 10px;
            }
            
            
            
            label{
            font-size: 18px;
            cursor: pointer;
            font-weight: 400;
            }
            .footer-button{
            margin-top: 20px;
            text-align: center;
            }
            
            .submitBtn{
            padding: 14px 30px;
            background-color: #0b03fc;
            color: #fff;
            border: 0;
            cursor: pointer;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 10px;
            transition: all .3s ease-in-out;
            }
            
            .submitBtn:hover {
            box-shadow: 0 14px 18px rgb(11, 3, 252, .28);
            }
            /* //////////////////////////////////////////////////////////////////////////////// */
            .succes {
            background-color: #4BB543;
            }
            .succes-animation {
            animation: succes-pulse 2s infinite;
            }
            
            .danger {
            background-color: #CA0B00;
            }
            .danger-animation {
            animation: danger-pulse 2s infinite;
            }
            
            .custom-modal {
            position: relative;
            width: 750%;
            min-height: 250px;
            background-color: #c5c5c587;
            color:#fff;
            border-radius: 30px;
            margin: 20px 0px;
            }
            .custom-modal .content { 
            position: absolute;
            width: 100%;
            text-align: center;
            bottom: 50px;
            }
            .custom-modal .content .type {
            font-size: 15px;
            color: #999;
            }
            .custom-modal .content .message-type {
            font-size: 20px;
            color: #000;
            }
            .custom-modal .border-bottom {
            position: absolute;
            width: 100%;
            height: 20px;
            border-radius: 0 0 30px 30px;
            bottom: -20px;
            }
            .custom-modal .icon-top {
            position: absolute;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            top: -30px;
            margin: 0 125px;
            font-size: 30px;
            color: #fff;
            line-height: 100px;
            text-align: center;
            }
            @keyframes succes-pulse { 
            0% {
            box-shadow: 0px 0px 30px 20px rgba(75, 181, 67, .2);
            }
            50% {
            box-shadow: 0px 0px 30px 20px rgba(75, 181, 67, .4);
            }
            100% {
            box-shadow: 0px 0px 30px 20px rgba(75, 181, 67, .2);
            }
            }
            @keyframes danger-pulse { 
            0% {
            box-shadow: 0px 0px 30px 20px rgba(202, 11, 0, .2);
            }
            50% {
            box-shadow: 0px 0px 30px 20px rgba(202, 11, 0, .4);
            }
            100% {
            box-shadow: 0px 0px 30px 20px rgba(202, 11, 0, .2);
            }
            }
            
            
            .page-wrapper {
            /*background-color: #eee;*/
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0px 0;
            }
            
            @media only screen and (max-width: 800px) {
            .page-wrapper {
            flex-direction: column;
            }
            }
            @media only screen and (min-width: 800px) {
            .modal-content {
            width:80%;   
            }
            }
            @media only screen and (max-width: 600px) {
            .quize {
            padding:17px;
            }
            .question-bar ul {
            padding:0px;   
            }
            h2 {
            font-size: 11px !important;
            line-height: 2.5 !important;
            }
            label{
            font-size: 12px !important;  
            }
            .page-title-content {
            padding: 22px 0 5px;
            }
            .custom-modal {
            width:100%;   
            }
            }
            .fnt-10{
            font-size:10px;
            }
    </style>
@endsection
    
<div class="page-content-wrapper bg-overlay">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
      <div class="container">
        <h6 class="mb-3 newsten-title text-capitalize">
            
        </h6>
      </div>
      <div class="container">
       
        <div class="container">
                <div class="col-lg-12 d-flex">
                 
               
                <a href="{{url('kpsc/psc-new-pattern/'.App\Models\NewmodalQa::where('id', '<', $id)->orderBy('id','desc')->pluck('id')->first())}}">
                  <button class="btn btn-info fnt-10 mt-3 btn-sm">
                       <span class="fa fa-arrow-left"></span>
                     മുൻ  ചോദ്യം
                  </button>
                </a>
                <a href="{{url('kpsc/psc-new-pattern/'.App\Models\NewmodalQa::orderBy('id','asc')->pluck('id')->first())}}">
                  <button class="btn fnt-10 btn-warning text-light mt-3 btn-sm">
                      ആദ്യം മുതൽ ആരംഭിക്കാം
                  </button>
                </a>
                
                <a href="{{url('kpsc/psc-new-pattern/'.App\Models\NewmodalQa::where('id', '>', $id)->min('id'))}}">
                  <button class="btn fnt-10 btn-success mt-3 btn-sm">
                      അടുത്ത ചോദ്യം
                      <span class="fa fa-arrow-right"></span>
                  </button>
                </a>   
                </div>
               
                <div class="col-lg-12 col-sm-12  row  rounded">
               
                <div class="quize">
                    <div class="question-bar">
                        <h2 class="qestion">
                            <strong>Q-</strong>
                           {!! $list_details->question !!}
                        </h2>
                        @php
                                $exp = explode(',,',$list_details['options']);
                                $option1 = str_replace(array('{', '}'), '',$exp[0]);
                                $option2 = str_replace(array('{', '}'), '',$exp[1]);
                                $option3 = str_replace(array('{', '}'), '',$exp[2]);
                                $option4 = str_replace(array('{', '}'), '',$exp[3]);
                        @endphp
                        <ul class=" row">
                            <div class="col-lg-6">
                            <li >
                                <input type="radio" name="option" id="a" class="ansList" value="{!! $option1 !!}">
                                <label for="a" class="ansa">
                                {!! $option1 !!}
                                </label>
                            </li>
                            </div>
                            <div class="col-lg-6">
                            <li >
                                <input type="radio" name="option" id="b" class="ansList" value="{!! $option2 !!}">
                                <label for="b" class="ansb">
                                {!! $option2 !!}
                                </label>
                            </li>
                            </div>
                            <div class="col-lg-6">
                                <li>
                                    <input type="radio" name="option" id="c" class="ansList" value="{!! $option3 !!}">
                                    <label for="c" class="ansc">
                                    {!! $option3 !!}
                                    </label>
                                </li>
                            </div>
                            <div class="col-lg-6">
                                <li>
                                    <input type="radio" name="option" id="d" class="ansList" value="{!! $option4 !!}">
                                    <label for="d" class="ansd" >
                                      {!! $option4 !!}
                                    </label>
                                </li>
                            </div>
                           
                           <br>
                           
                        </ul>
                        <div class="footer-button">
                        <button id="submit" class="submitBtn">Submit</button>
                        </div>
                      
                    </div>
                    <div id="showscore" class="scoreArea"></div>
                </div>
                
            
            </div>
                {!! bottom_single_ads() !!}}

            </div>
      </div>
  
    </div>
  </div>
  
  
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary d-none " id="Failed" data-toggle="modal" data-target="#exampleModalCenter"></button>
<button type="button" class="btn btn-primary d-none " id="Success" data-toggle="modal" data-target="#exampleModalCenter2"></button>
<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
       
        <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="fa fa-2x fa-close text-danger"></span>
        </button>
      </div>
      <div class="modal-body">
        <div class="page-wrapper">
            <div class="custom-modal">
                <div class="danger danger-animation icon-top "><i class="fa fa-times"></i></div>
                <div class="danger border-bottom"></div>
                <div class="content">
                    <p class="type text-danger">Answer is Incorrect</p>
                    <a href="{{url()->current()}}>"><button class="btn btn-info btn-md mt-2">Try Again 👍</button></a> 
                </div>
                
            </div>
        </div>
      </div>
       {!! bottom_single_ads() !!}
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="exampleModalCenter2" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
       
        <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true" class="fa fa-2x fa-close text-danger"></span>
        </button>
      </div>
      <div class="modal-body">
        <div class="page-wrapper">
            <div class="custom-modal">
                <div class="succes succes-animation icon-top"><i class="fa fa-check"></i></div>
                <div class="succes border-bottom"></div>
                <div class="content">
                <p class="type  text-success">Correct Answer ✌️</p>
                    <div class="col-lg-12  text-center ">
                      
                        <a href="{{url('kpsc/psc-new-pattern/'.App\Models\NewmodalQa::where('id', '<', $id)->orderBy('id','desc')->pluck('id')->first())}}">
                        <button class="btn btn-info btn-sm mt-2 d-inline ">Prev Question </button></a> &nbsp;&nbsp;&nbsp;&nbsp;
                      
                        <a href="{{url('kpsc/psc-new-pattern/'.App\Models\NewmodalQa::where('id', '>', $id)->min('id'))}}">
                        <button class="btn btn-success btn-sm mt-2 d-inline">Next Question </button></a>
                    </div>
                    
                </div>
               
            </div>
        </div>
      </div>
       {!! bottom_single_ads() !!}
    </div>
  </div>
</div>

@endsection

@section('scripts')

    <script>
        $(document).ready(function(){
            $(".submitBtn").click(function(){
               var click_ans = $('.ansList:checked').val();
               var question_no = '{{$list_details->id}}';
               
               if(click_ans === undefined){
                    alert('Choose any answer');
               }
               else{
                   var url = "{{url('kpsc/new-modal-qa-check-answer')}}";
                 
                   $.ajax({
                      url: url,
                      cache: false,
                      data: { click_ans : click_ans,question_no : question_no},
                      success: function(htl){
                         
                          if(htl == 1){
                            $("#Success").click();
                        }
                        else{
                            $("#Failed").click();
                        }
                      }
                    });
                   
            
                    
                   
               }
            });
            
        });
    </script>
@endsection
    