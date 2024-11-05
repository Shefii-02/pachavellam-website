@extends('cms.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Prev Question Paper Edit</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-3 text-end">
                    <!-- Short by filter -->
                    <a class="btn btn-sm btn-primary me-1 " href="{{url()->previous()}}"><i class="bi bi-arrow-left me-1"></i>Back</a>
                
                    <span class="js-choice ">
                    </span>
                </div>
            </div>
            
        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body">

            <!-- Search and select START -->
            <div class="row g-3 align-items-center justify-content-between mb-4">
             
                <div class="col-lg-12 ">
                    @can('Prev Qstn edit')
                    <form action="{{url(kpsc_cms('prev-qstn/update-prevqstn/'.$prev_qstn->id))}}" method="POST" enctype="multipart/form-data">
                        @csrf()
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                  <label class="form-label">Category</label>
                                <input list="categories" name="category" autocomplete="off" value="{{$prev_qstn->category}}" class="form-control" required >
                                <datalist id="categories">
                                    @foreach ($category as $item)
                                        <option value="{{ $item->category }}">
                                    @endforeach
                                </datalist>
                              
                               
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Sub Category</label>
                                
                                <input list="subcategory"  autocomplete="off" name="subcategory" value="{{$prev_qstn->subcategory}}"   class="form-control" required >
                                <datalist id="subcategory">
                                    @foreach ($sub_category as $item)
                                        <option value="{{ $item->subcategory }}">
                                    @endforeach
                                </datalist>
                           
                               
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Title</label>
                                <input class="form-control" required   autocomplete="off" type="text" value="{{$prev_qstn->title}}"  name="title"  >
                               
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Question Pdf</label>
                                <input class="form-control"   type="file" name="qstn_file" >
                                <div classs="col-md-2 ">
                                    
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Answer Pdf</label>
                                <input class="form-control"   type="file" name="ans_file" >
                                <div classs="col-md-2 ">
                                    <button type="submit" class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i> Update</button>
                                </div>
                            </div>
                        </div>
                    
                    </form>
                    @endcan
                       
                    
                    
                </div>
            </div>
            <!-- Pagination END -->
        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->

@endsection
