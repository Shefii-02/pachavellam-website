@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', $title ?? "KPSC-Result-" )
@section('content')
<style>
    .category-grid {
        font-weight: bold;
        border-radius:10px;
        padding: 22px;
        height: 100px;
        background-image: linear-gradient(to top, #30cfd0 0%, #330867 100%);
        margin-bottom: 10px;
    } 
    .category-grid1 {
        font-weight: bold;
        border-radius:10px;
        padding: 22px;
        height: 100px;
        background-image: linear-gradient(to right, #6a11cb 0%, #2575fc 100%);
        margin-bottom: 10px;
    }
    
    .category-grid2 {
        
        font-weight: bold;
        border-radius:10px;
        padding: 22px;
        height: 100px;
        background-image: linear-gradient(to top, #00c6fb 0%, #005bea 100%);
        margin-bottom: 10px;
    }
    
    .category-grid3 {
        
        font-weight: bold;
        border-radius:10px;
        padding: 22px;
        height: 100px;
        background-image: linear-gradient(15deg, #13547a 0%, #80d0c7 100%);
        margin-bottom: 10px;
    }
</style>

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-3 newsten-title">Results </h5>
            </div>
        </div>
        <div class="container ">
                    <div class=" col-lg-12">
            <p class="text-dark mob_text" style="line-height:2;">
               
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
                @foreach($results_type as $key => $type)
                    <div class="col-lg-4 col-md-4 col-6">
                        <a href="{{url('kpsc/psc-results/'.Str::slug($type->type))}}">
                            <div class="category-grid">
                                <span class=" fw-bold text-light mt-4 ">
                                    {{ucfirst($type->type)}}
                                </span>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
            

        </div>
        {!! bottom_double_ads() !!}}
  </div>
