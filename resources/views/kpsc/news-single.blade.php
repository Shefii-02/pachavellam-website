@extends('layouts.kpsc-app')
@extends('kpsc.section_header')
@section('title', "KPSC - Latest News/Article/Latest Informations")
@section('content')

@section('styles')
<style>
    .specialvideos img{
        border-radius: 9px;
    }

    .specialvideos a h6 {
        -webkit-transition-duration: 500ms;
        -o-transition-duration: 500ms;
        transition-duration: 500ms;
        font-size: 12px;
        margin-bottom: 0;
        position: absolute;
        left: 0.5rem;
        bottom: 0.5rem;
        z-index: 100;
        color: #ffffff;
        background-color: #19af64d1;
        padding: 0.25rem 0.625rem;
        border-radius: 2rem;
    }

    .specialvideos h6 {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
    }
    p img{
        width:100% !important;
        height: auto !important;
    }
    
            body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            }
            .pdfviewer {
            height: 100%;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            }
            .pdfviewer-container {
            margin: 0;
            padding: 0;
            display: flex;
            overflow: hidden;
            flex: 1;
            }
            .thumbnails {
            width: 150px !important;
            border: 1px solid #aaa;
            border-right: 3px solid #999;
            background: #ccc;
            }
            .thumbnails .pdfpage.selected {
            border: 2px solid #777;
            border-radius: 2px;
            }
            .maindoc {
            flex: 1;
            }
            .hide {
            display: none;
            }
            input[type="file"] {
            display: none;
            }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
            <script src="/pdf-viewer/js/pdfjs-viewer.js"></script>
            <link rel="stylesheet" href="/pdf-viewer/css/pdfjs-viewer.css">
            <link rel="stylesheet" href="/pdf-viewer/css/pdftoolbar.css">
            <script>
            var pdfjsLib = window['pdfjs-dist/build/pdf'];
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';
            </script>
@endsection
<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
      <div class="container">
        <div class="d-flex align-items-center justify-content-between">
          <h5 class="mb-3 newsten-title"></h5>
        </div>
      </div>

      
            <div class="container">
                <div class="row">
                    <div class="card col-lg-12">
                        <div class="rounded mx-auto d-block">
                        
                        </div>
                        <h6 class="card-header text-capitalize">{{$psc_news->title}}</h6>
               
                 
                        @if($psc_news->type == 'Pdf')
                    <div class="container mt-2 mb-5">
                        <h5>
                              ഡൗൺലോഡ് ഓപ്ഷൻ  👇👇👇👇👇
                        </h5>
                         {!! single_ads_show() !!}
                        <div class="col-lg-12 mt-3 mb-3">
                            <a href="{{ url(Storage::url('news/'.$psc_news->description)) }}" download="{{ url(Storage::url('news/'.$psc_news->description)) }}" class="btn btn-info rounded"><i class="fa fa-download"></i> Download Now</a>
                        </div>
                    </div>
                
            <div class="pdfviewer">
            <div class="pdftoolbar">
            <button class="pushed" onclick="togglethumbs(this);"><i class="material-icons-outlined">view_sidebar</i></button>
            <div class="v-sep"></div>
            <button onclick="pdfViewer.prev();"><i class="material-icons-outlined">arrow_upward</i></button>
            <div class="v-sep"></div>
            <button onclick="pdfViewer.next();"><i class="material-icons-outlined">arrow_downward</i></button>
            <input id="pageno" class="pageno" type="number" class="form-control form-control-sm d-inline w-auto" value="1" min="1" max="1000" onchange="pdfViewer.scrollToPage(parseInt(this.value))">
            <span id="pagecount" class="pageno"></span>
            <div class="divider"></div>
            <button onclick="pdfViewer.setZoom('in')"><i class="material-icons-outlined">add</i></button>
            <div class="v-sep"></div>
            <button onclick="pdfViewer.setZoom('out')"><i class="material-icons-outlined">remove</i></button>
            <div class="dropdown">
            <div class="dropdown-value" onclick="this.parentNode.classList.toggle('show');">
            <span class="zoomval">100%</span>
            <i class="material-icons-outlined">
            keyboard_arrow_down
            </i>                    
            </div>
            <div class="dropdown-content" onclick="this.parentNode.classList.toggle('show');">
            <a href="#" onclick='pdfViewer.setZoom("width"); return false;'>Adjust width</a>
            <a href="#" onclick='pdfViewer.setZoom("height"); return false;'>Adjust height</a>
            <a href="#" onclick='pdfViewer.setZoom("fit"); return false;'>Fit page</a>
            <a href="#" onclick='pdfViewer.setZoom(0.5); return false;'>50%</a>
            <a href="#" onclick='pdfViewer.setZoom(0.75); return false;'>75%</a>
            <a href="#" onclick='pdfViewer.setZoom(1); return false;'>100%</a>
            <a href="#" onclick='pdfViewer.setZoom(1.25); return false;'>125%</a>
            <a href="#" onclick='pdfViewer.setZoom(1.5); return false;'>150%</a>
            <a href="#" onclick='pdfViewer.setZoom(2); return false;'>200%</a>
            <a href="#" onclick='pdfViewer.setZoom(3); return false;'>300%</a>
            <a href="#" onclick='pdfViewer.setZoom(4); return false;'>400%</a>
            </div>                    
            </div>
            <div class="divider"></div>
            <label class="button" for="opendoc"><i class="material-icons-outlined">file_open</i></label>
            <input id="opendoc" type="file" accept="application/pdf">
            <a id="filedownload" class="button"><i class="material-icons-outlined">file_download</i></a>
            <div class="dropdown dropdown-right">
            <div onclick="this.parentNode.classList.toggle('show');">
            <button><i class="material-icons-outlined">keyboard_double_arrow_right</i></button>
            </div>
            <div class="dropdown-content" onclick="this.parentNode.classList.toggle('show');">
            <a href="#" onclick='pdfViewer.scrollToPage(1); return false;'><i class="material-icons-outlined">vertical_align_top</i>First page</a>
            <a href="#" onclick='pdfViewer.scrollToPage(pdfViewer.pdf.numPages); return false;'><i class="material-icons-outlined">vertical_align_bottom</i>Last page</a>
            <div class="h-sep"></div>
            <a href="#" onclick='pdfViewer.rotate(-90, true); pdfThumbnails.rotate(-90, true).then(() => pdfThumbnails.setZoom("fit"));'><i class="material-icons-outlined">rotate_90_degrees_ccw</i>Rotate countrary clockwise</a>
            <a href="#" onclick='pdfViewer.rotate(90, true); pdfThumbnails.rotate(90, true).then(() => pdfThumbnails.setZoom("fit"));'><i class="material-icons-outlined">rotate_90_degrees_cw</i>Rotate clockwise</a>
            <div class="h-sep"></div>
            <a href="#" onclick='document.querySelector(".pdfjs-viewer").classList.remove("horizontal-scroll"); pdfViewer.refreshAll();'><i class="material-icons-outlined">more_vert</i>Vertical scroll</a>
            <a href="#" onclick='setHorizontal()'><i class="material-icons-outlined">more_horiz</i>Horizontal scroll</a>
            </div>                    
            </div>                
            </div>
            <div class="pdfviewer-container">
            <div class="thumbnails pdfjs-viewer hide">
            </div>
            <div class="maindoc pdfjs-viewer">
            <div class="pdfpage placeholder">
            <p class="my-auto mx-auto">Cargue un fichero</p>
            </div>
            </div>    
            </div>
            </div>
            
            @section('scripts')
            <script>
            let PDFFILE="{{ url(Storage::url('news/'.$psc_news->description)) }}";
            function dataURItoBinArray(data) {
            // taken from: https://stackoverflow.com/a/11954337/14699733
            var binary = atob(data);
            var array = [];
            for(var i = 0; i < binary.length; i++) {
            array.push(binary.charCodeAt(i));
            }
            return new Uint8Array(array);
            }
            /** Function to load a PDF file using the input=file API */
            document.querySelector("#opendoc").addEventListener("change", function(e) {
            let file = e.target;
            let reader = new FileReader();
            reader.onload = async function() {
            await pdfViewer.loadDocument({data: dataURItoBinArray(reader.result.replace(/^data:.*;base64,/,""))});
            await pdfThumbnails.loadDocument({data: dataURItoBinArray(reader.result.replace(/^data:.*;base64,/,""))}).then(() => pdfThumbnails.setZoom("fit"));
            }
            if (file.files.length > 0) {
            reader.readAsDataURL(file.files[0]);
            document.querySelector('#filedownload').download = document.querySelector('#opendoc').files[0].name;
            }
            });
            /** Sets the document in horizontal scroll by changing the class for the pages container and refreshing the document 
            *    so that the pages may be displayed in horizontal scroll if they were not visible before */
            function setHorizontal() {
            document.querySelector(".maindoc").classList.add("horizontal-scroll"); 
            pdfViewer.refreshAll();    
            }
            /** Toggles the visibility of the thumbnails */
            function togglethumbs(el) {
            if (el.classList.contains('pushed')) {
            el.classList.remove('pushed');
            document.querySelector('.thumbnails').classList.add('hide');
            } else {
            el.classList.add('pushed');
            document.querySelector('.thumbnails').classList.remove('hide');
            }
            }
            /** Now create the PDFjsViewer object in the DIV */
            let pdfViewer = new PDFjsViewer($('.maindoc'), {
            zoomValues: [ 0.5, 0.75, 1, 1.25, 1.5, 2, 3, 4 ],
            
            /** Update the zoom value in the toolbar */
            onZoomChange: function(zoom) {
            zoom = parseInt(zoom * 10000) / 100;
            $('.zoomval').text(zoom + '%');
            },
            
            /** Update the active page */
            onActivePageChanged: function(page) {
            let pageno = $(page).data('page');
            let pagetotal = this.getPageCount();
            
            pdfThumbnails.setActivePage(pageno);
            $('#pageno').val(pageno);
            $('#pageno').attr('max', pagetotal);
            $('#pagecount').text('de ' + pagetotal);
            },
            
            /** zoom to fit when the document is loaded and create the object if wanted to be downloaded */
            onDocumentReady: function () {
            pdfViewer.setZoom('fit');
            pdfViewer.pdf.getData().then(function(data) {
            document.querySelector('#filedownload').href = URL.createObjectURL(new Blob([data], {type: 'application/pdf'}));
            document.querySelector('#filedownload').target = '_blank';
            });
            }
            });
            
            /** Load the initial PDF file */
            pdfViewer.loadDocument(PDFFILE).then(function() {
            document.querySelector('#filedownload').download = PDFFILE;
            });
            
            /** Create the thumbnails */
            let pdfThumbnails = new PDFjsViewer($('.thumbnails'), {
            zoomFillArea: 0.7,
            onNewPage: function(page) {
            page.on('click', function() {
            if (!pdfViewer.isPageVisible(page.data('page'))) {
            pdfViewer.scrollToPage(page.data('page'));
            }
            })
            },
            onDocumentReady: function() {
            this.setZoom('fit');
            }
            });
            
            pdfThumbnails.setActivePage = function(pageno) {
            this.$container.find('.pdfpage').removeClass('selected');
            let $npage = this.$container.find('.pdfpage[data-page="' + pageno + '"]').addClass('selected');
            if (!this.isPageVisible(pageno)) {
            this.scrollToPage(pageno);
            }
            }.bind(pdfThumbnails);
            
            pdfThumbnails.loadDocument(PDFFILE);
            </script>
            
            @endsection
            @else
                        <p class="card-body news-content">{!! $psc_news->description !!}</p>
            @endif
                    </div>
                   
                </div>
            </div>
            
            <div class="container">
                <blockquote>
<p><strong>&nbsp; You can do it. You have the potential to achieve what you desire.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; You are a deserving candidate. You have the capability to acquire the necessary skills just like any other competent individual.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; You are someone who can face and overcome adversities and challenges. You have already demonstrated that. Even if there are changes in your circumstances, you can adapt and continue to progress.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Develop a habit of studying with dedication for Kerala PSC examinations. Practice various mock tests and model question papers to enhance your preparation.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Maintain mental tranquility, as it is crucial for your success. Cultivate a positive mindset, self-confidence, determination, and self-motivation.<br /> {!! single_ads_show() !!}
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Work on improving your proficiency in the Malayalam language. Read extensively and practice practical usage.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Address any mental obstacles that may hinder your progress. Seek help from professionals who can provide solutions to your concerns.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Develop effective coping mechanisms to deal with stress and boost your self-esteem. Focus on personal growth and continuous improvement.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; With your sincere efforts and perseverance, you can achieve success in your endeavors.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Remember, you are capable of handling any situation and experiencing triumph. Embrace your achievements and strive for further growth.<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;<br />
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Even though I believe in your abilities, it is essential for you to have faith in yourself. May you experience significant progress and find contentment. May your aspirations be fulfilled. Best of luck to everyone!</strong></p>

                </blockquote>
                
            </div>
            
               {!! single_ads_show() !!}
    </div>
  </div>
@endsection