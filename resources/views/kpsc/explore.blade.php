@extends('layouts.kpsc-app')
@extends('kpsc.section_header')

@section('title', $title ?? "KPSC-Explore")

@section('content')
@section('styles')
<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap");
    :root {
      --primary-color: #22c473;
      --secondary-color: #e6eef9;
    }
    .container-tab {
      /* position: absolute; */
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .tabs {
      display: flex;
      position: relative;
      flex-wrap: nowrap !important; 
      /* background-color: #fff;
      box-shadow: 0 0 1px 0 rgba(24, 94, 224, 0.15), 0 6px 12px 0 rgba(180, 247, 25, 0.404); */
      padding: 0.75rem;
      border-radius: 9px;
      margin-top: 10px;
    }
    .tabs * {
      z-index: 2;
    }

    input[type=radio] {
      display: none;
    }

    .tab {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 54px;
      max-width: 200px;
      min-width: 200px;
      font-size: 1.25rem;
      font-weight: 500;
      border-radius: 99px;
      cursor: pointer;
      transition: color 0.15s ease-in;
    }


    input[type=radio]:checked + span {
      color: var(--primary-color);
    }

    input[id=radio-1]:checked ~ .glider {
      transform: translateX(0);
    }

    input[id=radio-2]:checked ~ .glider {
      transform: translateX(100%);
    }

    input[id=radio-3]:checked ~ .glider {
      transform: translateX(200%);
    }

    .glider {
      position: absolute;
      display: flex;
      height: 54px;
      width: 200px;
      background-color: var(--primary-color);
      z-index: 1;
      border-radius: 99px;
      transition: 0.25s ease-out;
    }

    @media (max-width: 700px) {
      .tabs {
        transform: scale(0.6);
      }
    }
</style>
@endsection

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<div class="page-content-wrapper">
    <div class="container-tab">
        <div class="tabs nav " role="tablist">
            <input type="radio" id="radio-1" name="tabs" checked />
            <span  data-bs-toggle="pill" href="#free_subject"><label class="tab free_subject text-dark" for="radio-1">Free</label></span>
            <input type="radio" id="radio-2" name="tabs" />
            <span data-bs-toggle="pill" href="#paid_subject"><label class="tab paid_subject text-dark" for="radio-2" >Premium</label></span>
            {{-- <input type="radio" id="radio-3" name="tabs" />
            <span data-bs-toggle="pill" href="#menu2"><label class="tab  text-dark" for="radio-3" >Series</label></span> --}}
            <span class="glider"></span>
        </div>
    </div>
   
    <!-- For You News Wrapper-->
    <div class="for-you-news-wrapper">
      <div class="container">
        <!-- Tab panes -->
        <div class="tab-content">
          <div id="free_subject" class="container tab-pane active"><br>
            <div class="row">
              @foreach ($category_free as $key => $list)
              <!-- Catagory Card-->
              <div class="col-lg-3 col-sm-3 col-sm-6 col-6  catagory-card">
                <a href="{{url('kpsc/free/'.$list->name_slug)}}">
                    <img src="{{ Storage::url($list->image) }}" alt="{{$list->category_name}}" class="w-100">
                    <h6>{{$list->category_name}}</h6>
                </a>
              </div>
                 {!! ads_space_sport($key) !!}
              @endforeach
            </div>
          </div>
          <div id="paid_subject" class="container tab-pane fade"><br>
            <div class="row">
              @foreach ($category_paid as $key => $list)
              <!-- Catagory Card-->
              <div class="col-lg-3 col-sm-3 col-sm-6 col-6 catagory-card mt-1">
                <a href="{{url('kpsc/premium/'.$list->name_slug)}}">
                  <img src="{{ Storage::url($list->image) }}" alt="{{$list->category_name}}" class="w-100">
                  <h6>{{$list->category_name}}</h6>
                </a>
              </div>
                 {!! ads_space_sport($key) !!}
              @endforeach
            </div>  
          </div>
          <div id="menu2" class="container tab-pane fade"><br>
            <div class="row">
              <!-- Single Recommended Post-->
                <div class="col-6 col-md-3">
                  <div class="single-recommended-post mt-3">
                    <!-- Customize Button-->
                    <div class="bookmark-customize-option">
                      <button class="btn dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="lni lni-more"></i></button>
                      <div class="dropdown-menu dropdown-menu-right">
                        <button class="dropdown-item" type="button"><i class="mr-1 lni lni-cut"></i>Remove</button>
                        <button class="dropdown-item" type="button"><i class="mr-1 lni lni-crop"></i>Edit</button>
                        <button class="dropdown-item" type="button"><i class="mr-1 lni lni-cog"></i>Settings</button>
                      </div>
                    </div>
                    <div class="post-thumbnail"><a href="{{url('kpsc/class')}}"><img src="https://www.pachavellam.com/assets/images/category/image/Science-1.jpg" alt=""></a></div>
                    <div class="post-content"><a class="post-catagory" href="{{url('kpsc/class')}}">Health</a></div>
                  </div>
                </div>
              
              </div>
            </div>
        </div>
   
      </div>
    </div>
  </div>

@endsection

