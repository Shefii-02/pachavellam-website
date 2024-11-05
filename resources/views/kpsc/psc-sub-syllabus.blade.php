@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Syllabus-".$syllabus_list[0]->type )
@section('content')
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title">Syllabus-{{$syllabus_list[0]->type}} </h5>
        </div>
      </div>

                            
      <div class="container">
                  <div class=" col-lg-12">
            <p class="text-dark mob_text" style="line-height:2;">
                
             നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
        </div>
        
            
            <div class='col-lg-12'>
                <p>Share This </p>
                <div class="social-share ">
                    <a href="https://wa.me/?text={{url()->current()}}നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                     "
                  
target='_blank' class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
                    <a href="https://www.facebook.com/sharer.php?u={{url()->current()}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
                    <a href="https://twitter.com/intent/tweet?text= 
                    വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് 2022 ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!
                    &url={{url()->current()}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={{url()->current()}}&title= 
                    വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് 2022 ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!
&summary=&source={{url('/')}}" target='_blank' class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
                </div><br>
                
            </div>
        @foreach($syllabus_list as $list)
          <!-- Single Trending Post-->
          <div class="single-trending-post d-flex">
            <div class="post-content">
              <a class="post-title" href="{{ Storage::url($list->file_name) }}" download="{{$list->title}}">
                {{$list->title}}
              </a>
              <div class="post-meta d-flex align-items-center">
                <a  href="{{ Storage::url($list->file_name) }}" download="{{$list->title}}">
                    <i class="fa fa-arrow-right"></i>   {{$list->category_no}}               
                </a>
              </div>
            </div>
            <div class="post-thumbnail text-right">
              <a  href="{{ Storage::url($list->file_name) }}" download="{{$list->title}}">
                <img src="{{url('psc/img/download-png.png')}}" alt="">
              </a>
            </div>
          </div>
        @endforeach
      </div>
      
    </div>
  </div>
@endsection