@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC- Material-")
@section('content')

@section('styles')
<style>
    .social-share a {
            padding: 0 .2em;
        }
    .linked-list{
        color: #0e76a8;
    }
    .twitter{
        color: #00acee;
    }
    .bg1{
        background-image: linear-gradient(to top, #4481eb 0%, #04befe 100%);
    }
    .bg2{
        background-image: linear-gradient(to right, #3ab5b0 0%, #3d99be 31%, #56317a 100%);
    }
    .bg3{
        background-image: linear-gradient(-20deg, #b721ff 0%, #21d4fd 100%);
    }
</style>
@endsection
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Psc Study Materials</h5>
            </div>
        </div>

                            
        <div class="container">
                    <div class=" col-lg-12">
            <p class="text-dark mob_text" style="line-height:2;">
                <br>
             നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
        </div>
        
            
            <div class='col-lg-12'>
                <p>Share This </p>
                <div class="social-share ">
                    <a href="https://wa.me/?text={{url()->current()}} 
                    വരാനിരിക്കുന്ന പരീക്ഷകൾക്ക് 2022 ലേ എല്ലാ കറണ്ട് അഫേഴ്സ് ചോദ്യങ്ങളും ഒരു കുടക്കീഴിൽ...!!"
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
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                            
                            
                             @if(count($material_type)>0)
                            @foreach ($material_type as $key => $list)
                                <div class="col-lg-4">
                                    <a href="{{url('kpsc/psc-material/'.Str::slug($list->type))}}" class="btn-btn-info text-light">
                                        <div class="card text-white  bg1 mb-3" style="max-width: 100%;">
                                        <div class="card-header">{{$list->type}}</div>
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-light">
                                                <a class="text-light" href="{{url('kpsc/psc-material/'.Str::slug($list->type))}}">
                                                   <u >open</u>
                                                </a>
                                            </h5>
                                        </div>
                                        </div>
                                    </a>
                                </div>  
                                {!! ads_space_sport($key) !!}
                            @endforeach
                           @else
                            <div class="mt-3">
                                <strong>
                                    മത്സരപരീക്ഷകൾക്ക് ആവശ്യമായ മെറ്റീരിയലുകൾ, സിലബസുകൾ എന്നിവ ഇവിടെ ഉടൻ ലഭ്യമാകും.
                                </strong>
                            </div>
                            @endif
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
  </div>
@endsection