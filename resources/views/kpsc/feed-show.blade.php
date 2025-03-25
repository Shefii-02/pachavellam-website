@extends('layouts.kpsc-app')
@extends('kpsc.section')
@section('title', $title ?? "KPSC-Feed")
@section('styles')

    <style>
        .content_feeds img{
            width:100%;
            max-height:300px;
        }
    
       .instagram i {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            color: red;
            z-index: 99;
            font-size: 40px;
            display: none;
        }
        .feed-card{
           
            flex-direction: column;
            align-items: center;
        }

        .product-card .card {
            margin: 5px;
            overflow: hidden;
        }
        .product-card .card .card-content {
            padding: 5px;
        }
        
        .product-card ul.card-action-buttons {
            /*margin: -18px 7px 0 0;*/
            text-align: left;
        }
        .product-card ul.card-action-buttons li {
            display: inline-block;
            padding-left: 7px;
        }
        .product {
            width: 20%;
            padding: 10px;
        }
        .product .card {
            margin: 0;
        }
        .product .card .card-content {
            padding: 5px 10px;
        }
        .chip {
            display: inline-block;
            height: 32px;
            font-size: 13px;
            font-weight: 500;
            color: rgba(0,0,0,0.6);
            line-height: 32px;
            padding: 0 12px;
            border-radius: 16px;
            background-color: #e4e4e4;
            margin-bottom: 5px;
            margin-right: 5px;
        }
            
        .feed-button{
                width: 30px;
                height: 30px;
                text-align: center;
                display: flex;
                align-items: center;
                justify-content: center;    
            }
        
        .like-button.liked {
          color: #ff6e6f;
          border-color: currentColor;
          filter: grayscale(0);
        }
        
        
        
        .liked .like-icon {
          -webkit-animation: heartPulse 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartPulse 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        .liked .like-icon [class^=heart-animation-] {
          background: url("data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMTgiIHZpZXdCb3g9IjAgMCAyMSAxOCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMTAuMTAxIDQuNDE3UzguODk1LjIwNyA1LjExMS4yMDdjLTQuNDY1IDAtMTAuOTY3IDYuODQ2IDUuMDgyIDE3LjU5MkMyNS4yMzcgNy4wMyAxOS42NjUuMjAyIDE1LjUwMS4yMDJjLTQuMTYyIDAtNS40IDQuMjE1LTUuNCA0LjIxNXoiIGZpbGw9IiNGRjZFNkYiIGZpbGwtcnVsZT0iZXZlbm9kZCIvPjwvc3ZnPg==") no-repeat center;
          background-size: 100%;
          display: block;
          position: absolute;
          top: 0;
          left: 0;
          width: 16px;
          height: 14px;
          opacity: 0;
        }
        .liked .like-icon [class^=heart-animation-]::before, .liked .like-icon [class^=heart-animation-]::after {
          content: "";
          background: inherit;
          background-size: 100%;
          width: inherit;
          height: inherit;
          display: inherit;
          position: relative;
          top: inherit;
          left: inherit;
          opacity: 0;
        }
        .liked .like-icon .heart-animation-1 {
          -webkit-animation: heartFloatMain-1 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartFloatMain-1 1s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        .liked .like-icon .heart-animation-1::before, .liked .like-icon .heart-animation-1::after {
          width: 12px;
          height: 10px;
          visibility: hidden;
        }
        .liked .like-icon .heart-animation-1::before {
          opacity: 0.6;
          -webkit-animation: heartFloatSub-1 1s 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartFloatSub-1 1s 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        .liked .like-icon .heart-animation-1::after {
          -webkit-animation: heartFloatSub-2 1s 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartFloatSub-2 1s 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
          opacity: 0.75;
        }
        .liked .like-icon .heart-animation-2 {
          -webkit-animation: heartFloatMain-2 1s 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartFloatMain-2 1s 0.1s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        .liked .like-icon .heart-animation-2::before, .liked .like-icon .heart-animation-2::after {
          width: 10px;
          height: 8px;
          visibility: hidden;
        }
        .liked .like-icon .heart-animation-2::before {
          -webkit-animation: heartFloatSub-3 1s 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartFloatSub-3 1s 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
          opacity: 0.25;
        }
        .liked .like-icon .heart-animation-2::after {
          -webkit-animation: heartFloatSub-4 1s 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
                  animation: heartFloatSub-4 1s 0.15s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
          opacity: 0.4;
        }
        
        .comment {
            border: 1px solid rgba(16, 46, 46, 1);
          
            float: left;
            border-radius: 5px;
            padding-left: 40px;
            padding-right: 30px;
            padding-top: 10px;
        }
        .comment img {
            vertical-align: middle;
            border-style: none;
        }
        .text-justify {
            text-align: justify!important;
        }
        .comment h4, .comment span, .darker h4, .darker span {
            display: inline;
        }
        
        @-webkit-keyframes heartPulse {
          0% {
            transform: scale(1);
          }
          50% {
            transform: scale(1.5);
          }
        }
        
        @keyframes heartPulse {
          0% {
            transform: scale(1);
          }
          50% {
            transform: scale(1.5);
          }
        }
        @-webkit-keyframes heartUnlike {
          50% {
            transform: scale(0.75);
          }
        }
        @keyframes heartUnlike {
          50% {
            transform: scale(0.75);
          }
        }
        @-webkit-keyframes heartFloatMain-1 {
          0% {
            opacity: 0;
            transform: translate(0) rotate(0);
          }
          50% {
            opacity: 1;
            transform: translate(0, -25px) rotate(-20deg);
          }
        }
        @keyframes heartFloatMain-1 {
          0% {
            opacity: 0;
            transform: translate(0) rotate(0);
          }
          50% {
            opacity: 1;
            transform: translate(0, -25px) rotate(-20deg);
          }
        }
        @-webkit-keyframes heartFloatMain-2 {
          0% {
            opacity: 0;
            transform: translate(0) rotate(0) scale(0);
          }
          50% {
            opacity: 0.9;
            transform: translate(-10px, -38px) rotate(25deg) scale(1);
          }
        }
        @keyframes heartFloatMain-2 {
          0% {
            opacity: 0;
            transform: translate(0) rotate(0) scale(0);
          }
          50% {
            opacity: 0.9;
            transform: translate(-10px, -38px) rotate(25deg) scale(1);
          }
        }
        @-webkit-keyframes heartFloatSub-1 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(13px, -13px) rotate(30deg);
          }
        }
        @keyframes heartFloatSub-1 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(13px, -13px) rotate(30deg);
          }
        }
        @-webkit-keyframes heartFloatSub-2 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(18px, -10px) rotate(55deg);
          }
        }
        @keyframes heartFloatSub-2 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(18px, -10px) rotate(55deg);
          }
        }
        @-webkit-keyframes heartFloatSub-3 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(-10px, -10px) rotate(-40deg);
          }
          100% {
            transform: translate(-50px, 0);
          }
        }
        @keyframes heartFloatSub-3 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(-10px, -10px) rotate(-40deg);
          }
          100% {
            transform: translate(-50px, 0);
          }
        }
        @-webkit-keyframes heartFloatSub-4 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(2px, -18px) rotate(-25deg);
          }
        }
        @keyframes heartFloatSub-4 {
          0% {
            visibility: hidden;
            transform: translate(0) rotate(0);
          }
          50% {
            visibility: visible;
            transform: translate(2px, -18px) rotate(-25deg);
          }
        }
        
        .pointer {cursor: pointer;}
        
    </style>

@endsection

@section('content')
<div class="page-content-wrapper">
    <!-- Element Wrapper-->
    <div class="element-wrapper">
    
      <div class="container">
        <div class="d-flex justify-content-center feed-card">
            @foreach($capsule_list as $key => $list)
                  <!-- single post -->
                <div class=" ">
                    <div class="product-card">
                        <div class="card bg-light z-depth-4">
                            @if($list->type == 'Image')
                            <div class="card-image instagram" >
                                 <i class="fa fa-heart"></i>
                                <img src="{{ Storage::url('files/'.$list->image) }}" alt="product-img" class="feed_like like-button"  data-id="{{$list->id}}">
                               
                            </div>
                            @else
                                <div class=" content_feeds instagram feed_like like-button"  data-id="{{$list->id}}">
                                    <i class="fa fa-heart"></i>
                                    {!! $list->content  !!}
                                </div>
                            @endif
                            <ul class="card-action-buttons">
                               
                                <li>
                                    <div class="pointer feed-button feed_like like-button  shadow-lg  rounded-circle bg-danger  like" data-id="{{$list->id}}">
                                        <a class="">
                                            <i class="fa fa-heart-o  text-light"></i>
                                        </a>
                                        <span class='like-icon'>
                                            <div class='heart-animation-1'></div>
                                            <div class='heart-animation-2'></div>
                                        </span>
                                    </div>
                                    
                                </li>
                                <li>
                                    <div class="pointer feed-button  shadow-lg rounded-circle bg-info feed_comments" data-id="{{$list->id}}">
                                        <a class="">
                                            <i class="fa fa-commenting-o  text-light"></i>
                                        </a>
                                    </div>
                                    
                                </li>
                                <li>
                                    <div class="pointer feed-button  shadow-lg rounded-circle bg-dark share" data-id="{{$list->id}}">
                                        <a href="https://wa.me?text={{url()->current()}}" target="_blank" class="">
                                            <i class="fa fa-share-alt  text-light  grey-text text-darken-3"></i>
                                        </a>
                                    </div>
                                </li>
                                <li class="bg-dark rounded float-right">
                                    <span class="text-light round">{{date('d-M-Y',strtotime($list->created_at))}}</span>
                                </li>
                            </ul>
                            <div class="row mt-3 ml-2">
                                
                                    <div style="width: 95%; margin: auto;">
                                        <div  data-id="{{$list->id}}" class=" feed_like like-button pointer chip like_count_{{$list->id}}">{{count(App\Models\CapsuleLike::where('cap_id',$list->id)->get())}} Likes</div>
                                        <div class="pointer chip comment_count_{{$list->id}} feed_comments" data-id="{{$list->id}}">{{count(App\Models\CapsuleComment::where('cap_id',$list->id)->get())}} Comments</div>
                                        
                                    </div>
                            </div>
                         
                        </div>
                    </div>
                </div>
              <!-- single post -->
              
                {!!small_ads_space_sport($key)!!}
            @endforeach
              
        </div>
      </div>
    </div>
  </div>

<!-- Modal -->
    <div class="modal fade" id="comments_show" tabindex="-1" aria-labelledby="comments_showModalLabel" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content">
                <div class="text-right m-2">  
                <i class="fa fa-times"  data-dismiss="modal"></i>
                </div>
              <div class="modal-body comments_showing">
                
              </div>
              <div class="modal-footer">
                <div class="input-group mb-3">
                  <form action="{{kpsc('feed-comments-store')}}" id="feed_comments_store"></form>
                      <input type="hidden" name="post_id"  form="feed_comments_store" id="post_id">
                 
                      <input type="text" required name="comments" form="feed_comments_store" class="form-control comments_input" placeholder="Enter Comments.." aria-label="Enter Comments.." aria-describedby="basic-addon2">
                      <div class="input-group-append">
                        <input type="submit"  form="feed_comments_store" class="btn btn-outline-secondary" value="Send">
                      </div> 
                  
                </div>
              </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(".like-button").on("click", function (e) {
          $(this).toggleClass("liked");
            $(this).addClass('bg-light');
            $(this).removeClass('bg-danger');
            $(this).find('.fa').addClass('fa-heart text-danger');
            $(this).find('.fa').removeClass('fa-heart-o text-light');
        });


        jQuery(document).ready(function ($) {
        
    
      //Like button effects
    
       
        $(".feed_comments").on("click", function (e) {
             var id_val = $(this).attr("data-id"); 
             $('#post_id').val(id_val);
             $.ajax({
              url: "{{kpsc('feed-comments')}}",
              data: {id : id_val},
              cache: false,
              success: function(result){
                  $('#comments_show').modal('show');
                  $(".comments_showing").html(result);
              }
            }).fail(function(err, status) {
             
            });
           
        });
         $('body').on('submit','#feed_comments_store', function(e){ 
            e.preventDefault();
            var data_val=$('#feed_comments_store').serialize();
            var post_id = $('#post_id').val();
            $.ajax({
              url: "{{kpsc('feed-comments-store')}}",
              data: data_val,
              cache: false,
              success: function(result){
                  $('.comments_input').val('');
                $(".comments_showing").html(result[0]);
                $(".comment_count_"+post_id).text(result[1] + ' Comments');
               
              }
            });
        });
        $(".feed_like").on("click", function (e) {
            var id_val = $(this).attr("data-id"); 
           
            $.ajax({
              url: "{{kpsc('feed-like')}}",
              data: {id : id_val},
              cache: false,
              success: function(result){
                $('.like_count_'+id_val).text(result + ' Likes');
              
              }
            }).fail(function(err, status) {
             
            });
        });
      
        $(".instagram").dblclick(function() {

                var heart = $(this).find("i");

                heart.fadeIn("slow");

                setTimeout(function() {
                    heart.fadeOut("slow");
                }, 2000);

            });
      
    });
  

    
    </script>

@endsection