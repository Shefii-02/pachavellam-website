@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Material-Semesters')
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-3 newsten-title">Study Material</h5>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-sm-12  d-flex justify-content-center">
                
                    <div class="col-lg-10 contact-main  rounded">
                        
                        <div class="container mt-3 row" >
                           
                         
                              @include('ug-pg.share-current-page')

                                @foreach ($list as $item)
                                @endforeach    
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a @if($list->where('semester_name','semester-1')->count()>0) href="{{ug_pg('materials/'.$university_name.'/'.$level_name.'/'.$course.'/semester-1')}}" @else href="#" style="display:none;" data-toggle="modal" data-target="#info_popup" @endif class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">
                                            Semester 1
                                            </h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a @if($list->where('semester_name','semester-2')->count()>0) href="{{ug_pg('materials/'.$university_name.'/'.$level_name.'/'.$course.'/semester-2')}}" @else href="#"  style="display:none;" data-toggle="modal" data-target="#info_popup" @endif class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">
                                                Semester 2
                                            </h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a @if($list->where('semester_name','semester-3')->count()>0) href="{{ug_pg('materials/'.$university_name.'/'.$level_name.'/'.$course.'/semester-3')}}" @else href="#"  style="display:none;" data-toggle="modal" data-target="#info_popup" @endif class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">
                                                Semester 3
                                            </h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a @if($list->where('semester_name','semester-4')->count()>0) href="{{ug_pg('materials/'.$university_name.'/'.$level_name.'/'.$course.'/semester-4')}}" @else href="#"  style="display:none;" data-toggle="modal" data-target="#info_popup" @endif class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">
                                                Semester 4 
                                            </h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a @if($list->where('semester_name','semester-5')->count()>0) href="{{ug_pg('materials/'.$university_name.'/'.$level_name.'/'.$course.'/semester-5')}}" @else href="#"  style="display:none;" data-toggle="modal" data-target="#info_popup" @endif class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">
                                                Semester 5
                                            </h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-4 col-12 h-25 text-center">
                                        <a @if($list->where('semester_name','semester-6')->count()>0) href="{{ug_pg('materials/'.$university_name.'/'.$level_name.'/'.$course.'/semester-6')}}" @else href="#"  style="display:none;" data-toggle="modal" data-target="#info_popup" @endif class="btn-btn-info text-light">
                                            <div class="card text-white  min-height-100 bg1 mb-3 w-100" >
                                            <h3 class="card-body mt-3  text-capitalize text-light h6">
                                                Semester 6
                                            </h3>
                                            
                                            </div>
                                        </a>
                                    </div>
                    
                         
                          
                          
                        </div>
                        
                    </div>
                  </div>
            </div>
        </div>
    </div>
    {!! single_ads_show() !!}
   
  </div>
  <!-- Modal -->
<div class="modal fade" id="info_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-dialog-centered" role="document">
    <div class="modal-content">
      
      <div class="modal-body p-2">
          <small>
               യൂണിവേഴ്സിറ്റി പരീക്ഷകൾക്ക് ആവശ്യമായ സിലബസ്, മെറ്റീരിയലുകൾ, ചോദ്യപേപ്പറുകൾ, ക്ലാസ്സുകൾ എന്നിവയാണ് ഈ വെബ്സൈറ്റിലൂടെ നിങ്ങൾക്ക് ലഭിക്കുക. തുടക്കം എന്ന നിലയിൽ ഓരോ പരീക്ഷാ സമയത്തും ആ പരീക്ഷയ്ക്ക് ആവശ്യമായ മെറ്റീരിയലുകൾ യഥാസമയം വെബ്സൈറ്റിൽ കൂട്ടിച്ചേർക്കുന്നതാണ്. സഹകരണം പ്രതീക്ഷിക്കുന്നു.
          </small>
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div> 
      {!! single_ads_show() !!}
    </div>
  </div>
</div>
@endsection