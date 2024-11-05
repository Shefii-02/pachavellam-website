@extends('layouts.without-bottom')
@extends('kpsc.section_header')
@php $title = "KPSC-Model Exam Answerkey"; @endphp
@section('title', $title ?? "KPSC-Model Exam" )
 @section('styles')
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
        <style>
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
            .pdftoolbar{
                display:none;
            }
        </style>
    @endsection
@section('content')
    <div id="mobileB1">  
          <!-- mobile-bottom-ads -->
        <ins class="adsbygoogle"
             style="display:inline-block;width:320px;height:50px"
             data-ad-client="ca-pub-2428477024574809"
             data-ad-slot="9817390780"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
    <div class="preloader" id="preloader" style="display:none">
        <div class="spinner-grow text-secondary" role="status">
            <div class="sr-only">Loading...</div>
        </div>
    </div>
    

    
    <div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
    <div class="trending-news-wrapper">
      
        <div class="container">
            <h4>
                Exam : <span class="text-info"> {{$examdetails->examtitle}}</span>
            </h4>
                            
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
                            <label class="button" for="opendoc">
                                <!--<i class="material-icons-outlined">file_open</i>-->
                                </label>
                            <!--<input id="opendoc" type="file" accept="application/pdf">-->
                            <a id="filedownload" class="button">
                                <!--<i class="material-icons-outlined">file_download</i>-->
                            </a>
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
                            
            
            <div class="container mt-4">
                <h6>Upload your exam details</h6>
                 <form action="{{url('kpsc/model-exam/store-result/'.$examdetails->id)}}" enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="form-group border-bottom mb-2 p-3 bg-light">
                            <div class="row">
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Full Name</label>
                                    <input class="form-control" required type="text" name="fullname" placeholder="" >
                                            
                                </div>
                                <div class="col-md-6 mt-2">
                                    <label class="form-label">Mobile Number</label>
                                    <input class="form-control" required oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1').replace(/^0[^.]/, '0');"  maxlength="10" type="text" name="mobile_no" placeholder=""  >     
                                </div>
                                 <div class="col-md-6 mt-2">
                                    <label class="form-label">No:of Correct Answer</label>
                                    <input class="form-control"  type="number" name="correct_answer" placeholder="" autocomplete="off" >
                                            
                                </div>
                                 <div class="col-md-6 mt-2">
                                    <label class="form-label">No:of Wrong Answer</label>
                                    <input class="form-control"  type="number" name="wrong_answer" placeholder="" autocomplete="off"  >
                                </div>
                              
                                <div class="col-md-12 mb-2 text-center mt-4">
                                   <button type="submit" class="btn btn-success">Save</button>
                                </div>

                            </div>
                        </div>
                          
                        </form>
            </div>
          
    
        </div>
      </div>
    </div>
   
 
    
    {!! single_ads_show() !!}
                <div class="container">
       <ul>
               
             <li> ✅ പരീക്ഷയുടെ ദൈർഘ്യം ഒരു മണിക്കൂർ 15 മിനിറ്റ്</li>
    
               <li> ✅ഓരോ ശരിയുത്തരങ്ങൾക്കും ഓരോ മാർക്ക് വീതം</li>
                
               <li> ✅ ഉത്തരം തെറ്റിയാൽ 1/3 നെഗറ്റീവ് മാർക്ക് ഉണ്ടായിരിക്കുന്നതാണ്</li>
                
               <li> ✅ അറ്റൻഡ് ചെയ്യാത്ത ചോദ്യങ്ങൾക്ക് മാർക്ക് നഷ്ടപ്പെടുന്നതല്ല</li>
                
               <li> ✅ ഇംഗ്ലീഷ് മലയാളം മാത്തമാറ്റിക് ജികെ എന്നീ വിഭാഗങ്ങളുടെ മാർക്ക് എടുത്ത് OMR ഷീറ്റിൽ എഴുതേണ്ടതാണ്</li>
                
                <li>✅ പരീക്ഷയെഴുതിയ ശേഷം നിങ്ങൾക്ക് ലഭിച്ച ആകെ ശരിയുത്തരങ്ങൾ, എത്ര ചോദ്യമാണ് തെറ്റിയത് ന്നിവ ഇവിടെ അപ്‌ലോഡ് ചെയ്യുക</li>
                
                <li>✅ നിശ്ചിത സമയത്തിനുള്ളിൽ ലീഡർ ബോർഡ് നിങ്ങൾക്ക് ലഭ്യമാകുന്നതാണ്</li>
               
       </ul>
   </div>

 
  
@endsection
      
    
      @section('scripts')
                            <script>
                            let PDFFILE="{{ url(Storage::url('model-exam/'.$examdetails->answer_file)) }}";
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
                            // document.querySelector("#opendoc").addEventListener("change", function(e) {
                            // let file = e.target;
                            // let reader = new FileReader();
                            // reader.onload = async function() {
                            // await pdfViewer.loadDocument({data: dataURItoBinArray(reader.result.replace(/^data:.*;base64,/,""))});
                            // await pdfThumbnails.loadDocument({data: dataURItoBinArray(reader.result.replace(/^data:.*;base64,/,""))}).then(() => pdfThumbnails.setZoom("fit"));
                            // }
                            // if (file.files.length > 0) {
                            // reader.readAsDataURL(file.files[0]);
                            // document.querySelector('#filedownload').download = document.querySelector('#opendoc').files[0].name;
                            // }
                            // });
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
                            // pdfViewer.pdf.getData().then(function(data) {
                            // document.querySelector('#filedownload').href = URL.createObjectURL(new Blob([data], {type: 'application/pdf'}));
                            // document.querySelector('#filedownload').target = '_blank';
                            // });
                            }
                            });
                            
                            /** Load the initial PDF file */
                            pdfViewer.loadDocument(PDFFILE).then(function() {
                            // document.querySelector('#filedownload').download = PDFFILE;
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
                               
            
             <script>
                setTimeout(function(){
                    $(".preloader").hide();
                }, 10000);

     </script>                   
                            
          
<script>
         document.addEventListener('contextmenu', function(e) {
           e.preventDefault();
         });


         document.onkeydown = function(e) {
           if(event.keyCode == 123) {
              return false;
           }
           if(e.ctrlKey && e.shiftKey && e.keyCode == 'I'.charCodeAt(0)) {
              return false;
           }
           if(e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) {
              return false;
           }
           if(e.ctrlKey && e.shiftKey && e.keyCode == 'J'.charCodeAt(0)) {
              return false;
           }
           if(e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) {
              return false;
           }
         }

</script>
@endsection
