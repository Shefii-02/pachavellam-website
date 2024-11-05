
@extends('admin.student.section')
	@section('content2')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                    <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Students List</h3>    
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    
                    <span class="js-choice ">
                        
                    </span>
                </div>
            </div>
            
        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body">
            
           <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text" id="filter">Filter</span>
              </div>
              <input type="text" class="form-control" data-table="table" data-count="#count" placeholder="Enter text to filter..." aria-label="Filter" aria-describedby="filter">
              
              <div class="col-lg-12">
                  <span class="total-count" id="count"></span>
              </div>
            </div>

            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover books">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th scope="col" class="border-0">#</th>
                            <th scope="col" class="border-0">Name</th>
                            <th scope="col" class="border-0">Email</th>
                            <th scope="col" class="border-0">Mobile No</th>
                            <th scope="col" class="border-0">Type</th>
                            <th scope="col" class="border-0">Created At</th>
                            <th scope="col" class="border-0 rounded-end">Action</th>
                        </tr>
                    </thead>

                    <!-- Table body START -->
                    <tbody>
                 @foreach ($list as $list)
                            
                        <!-- Table item -->
                            <tr>
                               
                                <td>{{$list->id}}</td>
                                <!-- Table data -->
                                <td>{{ucwords($list->name)}}</td>
                                <td>{{ucwords($list->email)}}</td>
                                <td>{{ucwords($list->mobile)}}</td>
                                <td>{{ucwords($list->type)}}</td>
                                <td>{{ucwords($list->created_at)}}</td>
                                <td>
                                    <a href="{{student_cms('student-list/'. $list->id.'/delete')}}" class="btn btn-sm text-center btn-circle btn-danger "><i class="bi bi-trash "></i></a>
                                </td>
                            </tr>
                        
                    @endforeach
                 
                    </tbody>
                    <!-- Table body END -->
                </table>
            </div>
            <!-- Course list table END -->

        </div>
        <!-- Card body START -->
    </div>
<!-- Main content END -->


@endsection

@section('scripts')
<script>
(function () {
  "use strict";

  var TableFilter = (function () {
    var search;

    function dquery(selector) {
      // Returns an array of elements corresponding to the selector
      return Array.prototype.slice.call(document.querySelectorAll(selector));
    }

    function onInputEvent(e) {
      // Retrieves the text to search
      var input = e.target;
      search = input.value.toLocaleLowerCase();
      // Get the lines where to search
      // (the data-table attribute of the input is used to identify the table to be filtered)
      var selector = input.getAttribute("data-table") + " tbody tr";
      var rows = dquery(selector);
      // Searches for the requested text on all rows of the table
      [].forEach.call(rows, filter);
      // Updating the line counter (if there is one defined)
      // (the data-count attribute of the input is used to identify the element where to display the counter)
      var writer = input.getAttribute("data-count");
      if (writer) {
        // If there is a data-count attribute, we count visible rows
        var count = rows.reduce(function (t, x) { return t + (x.style.display === "none" ? 0 : 1); }, 0);
        // Then we display the counter
        dquery(writer)[0].textContent = "No of Showing :"+ count;
      }
      
    }

    function filter(row) {
      // Caching the tr line in lowercase
      if (row.lowerTextContent === undefined)
        row.lowerTextContent = row.textContent.toLocaleLowerCase();
      // Hide the line if it does not contain the search text
      row.style.display = row.lowerTextContent.indexOf(search) === -1 ? "none" : "table-row";
      
        //  total_count_tr();
    }

    return {
      init: function () {
        // get the list of input fields with a data-table attribute
        var inputs = dquery("input[data-table]");
        [].forEach.call(inputs, function (input) {
          // Triggers the search as soon as you enter a search filter
          input.oninput = onInputEvent;
          // If we already have a value (following navigation back), we relaunch the search
          if (input.value !== "") input.oninput({ target: input });
        });
      }
   
    };

  })();

  TableFilter.init();total_count_tr();
})();


function total_count_tr(){
    var rowCount = $('tbody tr').length;
    $('.total-count').text("No of Showing : "+rowCount)
}
</script>
@endsection
