@foreach ($pscbulletin as $key => $list)
                        <div class="col-lg-12 mt-2  section-datewaise">
                    
                            <div class="d-flex">
                                <div class=" bg-green" style="    margin-top: 10px;height: 50px;width: 50px;border-radius: 50%;text-align: -webkit-center;">
                                    <a href="{{ Storage::url('bullet-in/'.$list->file_name) }}" style="margin: auto 0;" data-id="{{$list->id}}" download="{{$list->file_name}}" class="dwn_c text-center">
                                            <i class="fa fa-download text-light fa-2x mt-2" > </i>  
                                    </a>
                            
                                </div>
                                <div style="">
                                    <div class="text-dark  col-12 mt-3 pr-0">
                                    
                                    <a href="{{ Storage::url('bullet-in/'.$list->file_name) }}" data-id="{{$list->id}}" download="{{$list->file_name}}" class="dwn_c" > 
                                    <h6 class="sec-date-text text-dark" >
                                        <strong>
                                    {{$list->file_name}}
                                        </strong></h6>
                                    
                                        <p class="mt-1">Download</p></a>
                                    </div>   
                                </div>
                            </div>
                            <div class="col-lg-12 d-flex mt-3 p-0">
                                <div class="float-left col-sm-4 col-md-4 col-lg-4 p-0">
                                <small class="fa fa-download"> {{$list->download}} </small>
                                </div>
                                
                                <div class="float-right text-right col-sm-8 col-md-8 col-lg-8 p-0">
                                    <p><strong class=" "><?php echo date("M-Y", strtotime($list->month_year)); ?>  Issue-{{$list->issue}}</strong></p>
                                </div>
                            
                            
                            </div>
                    
                        </div>
                             
                        @endforeach