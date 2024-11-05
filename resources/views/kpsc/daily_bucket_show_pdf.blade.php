@extends('layouts.kpsc-app')
    @extends('kpsc.section_header')
    @php
        $title = "Daily-Material-Show";
    @endphp
    @section('title',  $title)
    @section('content')


    <div class="preloader" id="preloader">
      <div class="spinner-grow text-secondary" role="status">
        <div class="sr-only">Loading...</div>
      </div>
    </div>
    

<div class="page-content-wrapper">
    <!-- Trending News Wrapper-->
     
    <div class="trending-news-wrapper">
           <div class="container mt-3 row">
                <div class=" col-lg-12">
                    <p class="text-dark mob_text" style="line-height:2;">
                        നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക                        </p>
                </div>

    
                <div class="col-lg-12">
                    <p>Share This </p>
                        <div class="social-share ">
                            <a href="https://wa.me/?text=https://pachavellam.com നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക" target="_blank" class="sharer button"><i class="fa fa-2x fa-whatsapp text-success "></i></a>
                            <a href="https://www.facebook.com/sharer.php?u=https://pachavellam.com" target="_blank" class="sharer button"><i class="fa fa-2x fa-facebook-square text-primary"></i></a>
                            <a href="https://twitter.com/intent/tweet?text= നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക&amp;url=https://pachavellam.com" target="_blank" class="sharer button"><i class="fa fa-2x fa-twitter-square twitter "></i></a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&amp;url=https://pachavellam.com&amp;title= നിങ്ങൾക്ക് ഉപകാരപ്പെട്ടെങ്കിൽ നിങ്ങളുടെ കൂട്ടുകാർക്ക് കൂടി ഷെയർ ചെയ്യുക&amp;summary=&amp;source=https://pachavellam.com" target="_blank" class="sharer button"><i class="fa fa-2x fa-linkedin-square linked-list "></i></a>
                        </div>
                    
                </div>   
                
                <div class=" col-lg-12">
                    <h6 class="text-center text-danger font-bold">
                        മെറ്റീരിയൽ ഡൗൺലോഡ് ഓപ്ഷൻ ചുവടെ നൽകിയിരിക്കുന്നു  👇👇👇👇👇
                    </h6>
                </div>
            </div>
          {!! single_ads_show() !!}
        <div class="container">
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
            </style>
            @endsection
            
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
            
            @section('scripts')
            <script>
                let PDFFILE= "{{ Storage::url('daily-buckets/'.$url) }}";
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
                
                
                
                
                setTimeout(function(){
                    $(".preloader").hide();
                }, 10000);

            </script>
            
            @endsection
        </div>
        
            <div class="container mt-2 mb-2">
                <h5>
                      മെറ്റീരിയൽ ഡൗൺലോഡ് ഓപ്ഷൻ  👇👇👇👇👇
                </h5>
                <div class="col-md-4">
                    <div style="height:200px;text-align:center" class="border">
                        <a class="btn btn-info" style="position: absolute;bottom: 0;" href="{{ Storage::url('daily-buckets/'.$url) }}" download="{{'Study-Material-'.date('Y-m-d-H-i-s-a')}}">Download</a>
                    </div>
                </div>
        
            </div>
      {!! single_ads_show() !!}
            <div class="container">
                <div class="container mt-3 row" >
                    ഇന്നത്തെ മെറ്റീരിയൽ
                </div>
                    
                    @foreach($dailyBucket_list as $key => $list)
                      <!-- Single Trending Post-->
                        <div class="single-trending-post d-flex">
                            <div class="post-content">
                             
                              <span class="badge badge-pill badge-primary ">{{$list->type}}</span><br>
                              <span class="text-dark"> {{$list->title}} </span><br>
                                @if($list->type == 'Pdf')
                                  
                                    <a class="float-right badge badge-pill badge-success p-2" href="{{url('kpsc/daily-bucket/pdf/'.$list->class_date.'/'.$list->content)}}" target="_new">
                                        Open
                                    </a>
                                    
                                    
                                @elseif($list->type == 'Voice Msg')
                                
                                    <audio controls>
                                        
                                      <source src="{{ Storage::url('daily-buckets/'.$list->content) }}" type="audio/ogg">
                                      <source src="{{ Storage::url('daily-buckets/'.$list->content) }}" type="audio/mpeg">
                                    
                                    </audio>
                                
                                @elseif($list->type == 'Text Msg')
                                  {!! $list->content !!}
                                @elseif($list->type == 'Link')
                                
                                    <a  class="float-right badge badge-pill badge-success p-2" href="{{$list->content}}" target="_new">Open</a>
                                @else
                                    -------
                                @endif
                            </div>
                        </div>
                       
                    @endforeach    
        @include('ug-pg.share-current-page')
                      </div>
            </div>
      
    </div>
</div>
@endsection