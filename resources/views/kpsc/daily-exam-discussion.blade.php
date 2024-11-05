@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Daily Exam Discussion-" )

@section('styles')
<style>
    
.comment-img {
  width: 2rem;
  height: 2rem;
}

.form-floating {
    position: fixed;
    bottom: 0;
    background: #fff;
    width:80%;
    z-index: 9999999;
  
}

</style>

@endsection
@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
       

          <h6>
             Exam Title
          </h6> 
          {!! single_ads_show() !!}
 
      <div class="col-md-12">
         <div class="rounded-3 ">
            <div class="mb-5">

            @foreach($list as $key=> $listing)
            <!-- Comment #1 //-->
               <div class="bg-gray rounded p-2 mb-2">
                  <div class="comment">
                      <div class="d-flex">
                          <img class="rounded-circle comment-img"
                          src="{{ Storage::url('users/'.$listing->sender_img) }}" />
                          <div class="ml-1"><a href="#" class="fw-bold link-dark ml-1">{{$listing->sender_name}}</a> 
                          <!--<span class="text-muted text-nowrap">2 days ago</span>-->
                          </div>
                      </div>
                     
                     <div class="flex-grow-1 mt-2">
                        
                        <div class="mt-2">
                           {{$listing->comment}}
                        </div>
                        
                     </div>
                     
                  </div>
                  @if($listing->reply != null)
                    <div class="comment ml-3 mt-2 bg-light rounded p-2">
                      <div class="d-flex">
                          <img class="rounded-circle comment-img"
                          src="{{ Storage::url('users/'.$listing->reply_img) }}" />
                          <div class="ml-1"><a href="#" class="fw-bold link-dark ml-1">{{$listing->reply_name}}</a>
                          <!--<span class="text-muted text-nowrap">2 days ago</span>-->
                          </div>
                      </div>
                     
                     <div class="flex-grow-1 mt-2">
                        
                        <div class="mt-2">
                            {{$listing->reply}}
                        </div>
                        
                     </div>
                     
                    </div>
                    @endif
               </div>

            <!-- End Comment #1 //-->
            @endforeach
            </div>
            
            <!-- Start Comment Enetr #1 //-->
            
            <div class="form-floating ">
                @auth
                <form action="" method="POST">
                    @csrf()
                    <div class="mt-2">
                      <textarea required class="form-control w-100"
                                placeholder="Leave a comment here"
                                rows="3" name="comment"></textarea>
                   </div>
                   <div class="float-right mt-2 mb-2">
                      <button type="submit" class="btn btn-sm btn-primary text-uppercase">comment</button>
                   </div> 
                </form>
                @else
                <div class="mt-2">
                      <textarea required class="form-control w-100"
                                placeholder="Please login after comment here"
                                rows="3" name="comment"></textarea>
                   </div>
                   <div class="float-right mt-2 mb-2">
                      <a href="{{url('kpsc/daily-exam/login')}}" class="btn btn-sm btn-primary text-uppercase">Login</a>
                   </div> 
                
                @endauth
            </div>
            
            <!-- End Comment Enetr #1 //-->
            
            
            
            
         </div>
      </div>
 

  


      </div>
      
    </div>
         
             <!-- Ads -->
            {!! single_ads_show() !!}
  </div>
@endsection