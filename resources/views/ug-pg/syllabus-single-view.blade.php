@extends('layouts.ug-pg-app')
@extends('ug-pg.section_header')
@section('title', $title ?? 'University-Syllabus')
@section('content')

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
    
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
              <h5 class="mb-3 newsten-title">{{$title}}</h5>
            </div>
        </div>
        <div class="container">
             <div class="alert alert-warning alert-dismissible fade " role="alert">
              <strong><i class="fa fa-warning "></i> if you don't see the material on this page, please refresh again!</strong>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
        
            @if($list->type == 'Pdf Type')
                <iframe src="https://docs.google.com/viewer?url={{ url(Storage::url('syllabus/'.$list->file)) }}&embedded=true" width="100%" height="500px" allow="autoplay"></iframe>
            @else
                <iframe src="https://drive.google.com/file/d/1a9WA9zGMnT-UixOy2Se-3dOsBhxKTm4G/preview" width="100%" height="500px" allow="autoplay"></iframe>
            @endif
    
    
            @include('ug-pg.share-current-page')
                              
        </div>
      </div>
       
                {!! single_ads_show() !!}
                 
                 
                 {!! single_ads_show() !!}
    </div>
  </div>
@endsection

 @section('scripts')
    <script> 
    
        // JavaScript anonymous function
        (() => {
            if (window.localStorage) {

                // If there is no item as 'reload'
                // in localstorage then create one &
                // reload the page
                if (!localStorage.getItem('reload')) {
                    localStorage['reload'] = true;
                    window.location.reload();
                    // alert('waiting..')
                } else {
  
                    // If there exists a 'reload' item
                    // then clear the 'reload' item in
                    // local storage
                    localStorage.removeItem('reload');
                    if ($('iframe').contents().find('body').children().length > 0) {
                        // alert('is loaded');
                    } else {
                        $('.alert').addClass('show');
                        // alert('is not loaded');
                    }
                }
            }
        })(); // Calling anonymous function here only
    </script>
@endsection