@extends('cms.section')
@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-8">
                    <h3 class="mb-0">Psc News</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-3 text-end">
                    @can('Psc News add')
                        <!-- Short by filter -->
                        <button class="btn btn-sm btn-primary me-1 add_new_banner"><i class="bi bi-plus me-1"></i>Add New</button>
                    @endcan
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

                <div class="col-lg-12 new-banner-section " style="display: none;">
                    @can('Psc News add')
                        <form action="" method="POST" enctype="multipart/form-data">
                            @csrf()
                            <div class="row">

                                <div class="col-md-5">
                                    <div
                                        class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                        <!-- Image -->
                                        <img src="/assets/images/element/gallery.svg" id="uploaded-image"
                                            class="uploaded-image h-50px mb-2" alt="">
                                        <div>
                                            <label style="cursor:pointer;">
                                                <span>
                                                    <input class="form-control stretched-link" type="file" accept="image/*"
                                                        id="pic" name="my-image"
                                                        accept="image/gif, image/jpeg, image/png" />
                                                    <input type="hidden" name="image" id="picture" />
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7 row">
                                    <div class="col-md-12">
                                        <label class="form-label">Title</label>
                                        <input class="form-control" type="text" name="title" placeholder="Enter Title">
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-control type_file" name="type">
                                            <option value="Text">Text</option>
                                            <option value="Pdf">Pdf</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label class="form-label">Post Date</label>
                                        <input class="form-control" type="date" name="date" placeholder="Enter Title">
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div class="text-editor mb-2">
                                        <textarea name="content" id="editor" rows="10">
                                      
                                    </textarea>
                                    </div>
                                    <div class="file--input form-group mb-2" style="display:none">
                                        <input type="file" name="file" class="form-control">
                                    </div>
                                    <div classs="col-md-2 ">
                                        <button class="btn btn-success prev-btn mb-0 mt-3"><i class="bi bi-check me-1"></i>
                                            Submit</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    @endcan



                </div>
            </div>
            <!-- Search and select END -->
            @can('Psc News list')
                <!-- Course list table START -->
                <div class="table-responsive border-0">
                    <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                        <!-- Table head -->
                        <thead>
                            <tr>
                                <th scope="col" class="border-0 rounded-start">Image</th>
                                <th scope="col" class="border-0">Title</th>
                                <th scope="col" class="border-0">Posted Date</th>
                                <th scope="col" class="border-0">Status</th>
                                <th scope="col" class="border-0 rounded-end">Action</th>
                            </tr>
                        </thead>

                        <!-- Table body START -->
                        <tbody>
                            @foreach ($psc_news as $news_list)
                                <!-- Table item -->
                                <tr>
                                    <!-- Table data -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <!-- Image -->
                                            <div class="w-100px">
                                                <img src="{{ Storage::url('files/' . $news_list->image) }}" class="rounded"
                                                    alt="">
                                            </div>

                                        </div>
                                    </td>

                                    <!-- Table data -->
                                    <td>{{ $news_list->title }}</td>
                                    <td>{{ $news_list->post_date }}</td>
                                    <td>
                                        @if ($news_list->status == 1)
                                            <span class="badge bg-orange text-white">Show</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Hide</span>
                                        @endif
                                    </td>


                                    <!-- Table data -->
                                    <td>
                                        @can('Psc News edit')
                                            @if ($news_list->type == 'Text')
                                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Reset"
                                                    aria-label="Reset" href="#" data-title="{{ $news_list->title }}"
                                                    data-date="{{ $news_list->post_date }}"
                                                    data-image="{{ url(Storage::url('files/' . $news_list->image)) }}"
                                                    data-content="{{ $news_list->description }}" data-id="{{ $news_list->id }}"
                                                    class="btn btn-sm btn-info  reset-banner btn-circle"><i
                                                        class="bi bi-pencil"></i></a>
                                            @endif
                                        @endcan
                                        @can('Psc News delete')
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"
                                                aria-label="Delete" data-id="{{ $news_list->id }}"
                                                href="{{ route('adminkpsc.psc-news.delete', $news_list->id) }}"
                                                class="btn btn-sm btn-danger  btn-circle"><i class="bi bi-trash "></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                        <!-- Table body END -->
                    </table>
                </div>
                <!-- Course list table END -->
            @endcan
            <!-- Pagination START -->
            {{-- <div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-4 mt-sm-3">
                <!-- Content -->
                <p class="mb-0 text-center text-sm-start">Showing 1 to 8 of 20 entries</p>
                <!-- Pagination -->
                <nav class="d-flex justify-content-center mb-0" aria-label="navigation">
                    <ul class="pagination pagination-sm pagination-primary-soft d-inline-block d-md-flex rounded mb-0">
                        <li class="page-item mb-0"><a class="page-link" href="#" tabindex="-1"><i class="fas fa-angle-left"></i></a></li>
                        <li class="page-item mb-0"><a class="page-link" href="#">1</a></li>
                        <li class="page-item mb-0 active"><a class="page-link" href="#">2</a></li>
                        <li class="page-item mb-0"><a class="page-link" href="#">3</a></li>
                        <li class="page-item mb-0"><a class="page-link" href="#"><i class="fas fa-angle-right"></i></a></li>
                    </ul>
                </nav>
            </div> --}}
            <!-- Pagination END -->
        </div>
        <!-- Card body START -->
    </div>
    <!-- Main content END -->

    <!-- Modal -->
    <div class="modal fade" id="edit_banner" tabindex="-1" role="dialog" aria-labelledby="edit_banner_modal"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="edit_banner_modal">Edit News</h5>
                    <button type="button" class="close" onclick="$('#edit_banner').modal('hide');"
                        data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/admin/kpsc/psc-news/update-pscnews" method="POST">
                    <div class="modal-body">

                        @csrf()
                        <div class="editbanner">
                            <div class="row">

                                <div class="col-md-6">
                                    <div
                                        class="text-center justify-content-center align-items-center p-4 p-sm-5 border border-2 border-dashed position-relative rounded-3">
                                        <!-- Image -->
                                        <img src="/assets/images/element/gallery.svg" id="uploaded-image"
                                            class="uploaded-image h-50px mb-2" alt="">
                                        <div>
                                            <label style="cursor:pointer;">
                                                <span>
                                                    <input class="form-control stretched-link" type="file"
                                                        accept="image/*" id="pic2" name="my-image"
                                                        accept="image/gif, image/jpeg, image/png" />
                                                    <input type="hidden" name="image" id="picture"
                                                        class="picture" />
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 row">
                                    <div class="col-md-12">
                                        <label class="form-label">Title</label>
                                        <input class="form-control title" type="text" name="title"
                                            placeholder="Enter Title">
                                        <input type="hidden" name="id" class="id">
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-control" name="type">
                                            <option value="Text">Text</option>
                                            <option value="Pdf">Pdf</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mt-3">
                                        <label class="form-label">Post Date</label>
                                        <input class="form-control date" type="date" name="date"
                                            placeholder="Enter Title">
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <div class="text-editor">
                                        <textarea name="content" class="ckeditor" id="editor1" rows="10">
                                  
                                </textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">

                        <button type="submit" class="btn btn-success prev-btn mb-0 "><i class="bi bi-check me-1"></i>
                            Submit</button>

                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal uploadimageModal" tabindex="-1" role="dialog" id="uploadimageModal">
        <div class="modal-dialog" role="document" style="min-width: 700px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Image</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="image_demo"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="preview_target" value="" />
                    <input type="hidden" id="input_target" value="" />
                    <button type="button" class="btn btn-primary crop_image">Crop and Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.js"></script>
    <script>
        $(document).ready(function() {

            $(".add_new_banner").click(function() {

                $(".new-banner-section").fadeToggle(1000);
            });
        });
        var image_crop = $('.image_demo').croppie({
            viewport: {
                width: 252,
                height: 152
                // type: 'square'
            },
            boundary: {
                width: 252,
                height: 152
            }
        });
        /// catching up the cover_image change event and binding the image into my croppie. Then show the modal.
        $('#pic,#pic2').on('change', function() {
            var reader = new FileReader();
            reader.onload = function(event) {
                image_crop.croppie('bind', {
                    url: event.target.result,
                });
            }
            reader.readAsDataURL(this.files[0]);
            $('#uploadimageModal').modal('show');

        });


        $('.crop_image').click(function(event) {
            image_crop.croppie('result', {
                type: 'canvas',
                format: 'png'
            }).then(function(response) {
                $("#uploaded-image2,#uploaded-image").attr("src", response);
                $(".picture").val(response);
            });
            $("#pic,#pic2").val("");
            $('#uploadimageModal').modal('hide');
        });
        $('.reset-banner').click(function(event) {

            var id = $(this).data('id');
            var title = $(this).data('title');
            var date = $(this).data('date');
            var image = $(this).data('image');
            var content = $(this).data('content');



            $('.title').val(title);
            $('.date').val(date);


            $('.id').val(id);
            $('.picture').val(image);
            $(".uploaded-image").attr("src", image);
            $('#edit_banner').modal('show');
            CKEDITOR.instances['editor1'].setData(content);

        });

        $('body').on('change', '.type_file', function() {

            if ($(this).val() == 'Pdf') {
                $('.file--input').show()
                $('.text-editor').hide()
            } else {
                $('.file--input').hide()
                $('.text-editor').show()
            }

        });
    </script>
@endsection
