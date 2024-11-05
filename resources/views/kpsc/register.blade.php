@php 
    $title = "Register::Information";
@endphp
@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC")
    
@section('content')
    <div class="page-content-wrapper">
        <!-- Trending News Wrapper-->
        <div class="trending-news-wrapper">
            
            <div class="container">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-3 newsten-title">Fill Your Details </h5>
                </div>
            </div>
           
            <div class="container"> 
                    @if (count($errors) > 0)
                       <div class = "alert alert-danger">
                          <ul>
                             @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                <hr>
                             @endforeach
                          </ul>
                       </div>
                    @endif
                <div class="col-lg-12">
                    <form action="{{url('kpsc/register')}}" method="POST"  autocomplete="off" >
                        @csrf()
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text"  autocomplete="off" readonly value="{{auth()->user()->name}}" class="form-control" required  name="name" >
                        </div>
                        <div class="form-group">
                            <label>Email Id</label>
                           
                            <input type="text"  autocomplete="off" value="{{auth()->user()->email}}" readonly class="form-control" required  name="email_id" >
                        </div>
                        
                        <div class="form-group">
                            <label>Enter Mobile Number (without  country code [0,91,+91])</label>
                           
                            <input type="text"  autocomplete="off" maxlength="10" value="{{ old('mobile_no')}}"   class="form-control" required  name="mobile_no" onkeypress="return /[0-9]/i.test(event.key)">
                        </div>
                        
                        <div class="form-group">
                            <label>Enter Password</label>
                             <div class="float-right">
                                <!-- An element to toggle between password visibility -->
                                <input type="checkbox" onclick="myFunction()">Show Password
                            </div>
                            <input type="password"  autocomplete="new-password"   value="{{ old('password')}}"  required  class="form-control " id="shw_pswd"     name="password">
                        </div>
                        
                        <div class="form-group">
                            <label>Enter Confirm Password</label>
                           
                            <input type="text" autocomplete="new-password" value="{{ old('confirmed')}}"  required class="form-control" name="confirmed">
                        </div>
                        
                        <div class="form-group text-center">
                            <input type="submit" class="btn btn-secondary" name="submit_btn" value="Submit">
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function myFunction() {
              var x = document.getElementById("shw_pswd");
              if (x.type === "password") {
                x.type = "text";
              } else {
                x.type = "password";
              }
            }
    </script>
@endsection
