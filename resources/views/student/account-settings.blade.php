@php 
    $title = "Login/Signup";
@endphp

@extends('web.layout')
@section('title', $title ?? "KPSC")

@section('styles')

<style>

.frame {
    min-height: 150px;
    /*width: 420px;*/
       background-color:#e9ecefa8;
    margin-left: auto;
    margin-right: auto;
    border-top: solid 1px rgba(255, 255, 255, .5);
    border-radius: 15px;
    box-shadow: 0px 2px 7px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    transition: all .5s ease
      text-shadow: 0 0 3px #FF0000;
}
</style>
@endsection

@section('content')
<div class="page-content-wrapper">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
    	<div class="container">
            <div class="d-flex justify-content-center mt-6">
                <div class="col-lg-4 mb-5">
                    
                  <div class="frame">
                      <h2>Fill Additional Details</h2>
                    	<form action="{{route('account-settings-submit')}}" method="POST">
                    	    @csrf()
                    	    <div class="m-3">
                                <label for="name" class="form-label text-dark">Full Name</label>
                                <input type="text" class="form-control bg-light" id="name" name="name" required value="{{auth()->user()->name}}">
                                <div id="mobile_no_error" class="text-danger">{{$errors->first('name')}}</div>
                            </div>
                            <div class="m-3">
                                <label for="email" class="form-label text-dark">Email</label>
                                <input type="email" class="form-control bg-light" id="email" name="email" required  value="{{auth()->user()->email}}">
                                <div id="mobile_no_error" class="text-danger">{{$errors->first('email')}}</div>
                            </div>
                	       
                	        <div class="m-3">
                                <label for="mobile_no" class="form-label text-dark">Mobile Number</label>
                                <div class="input-group mb-3">
                                     <span class="input-group-text" id="basic-addon1">+91</span>
                                    <input type="text"  minlength="10" maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');"  class="form-control bg-light" id="mobile_no"  required name="phone" @if(auth()->user()->mobile != null) readonly @endif value="{{str_replace('+91', '',auth()->user()->mobile)}} ">
                                </div>
                                <div id="mobile_no_error" class="text-danger">{{$errors->first('phone')}}</div>
                            </div>
                            <div class="text-center">
                	        <button type="submit" class="btn btn-primary">Save</button>
                                
                            </div>
                    	</form>
                    </div>
                </div>
           
               
            </div> 
        </div>
    	</div>
    </div>
@endsection