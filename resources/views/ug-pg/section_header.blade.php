@section('header-tab')
<!-- Header Area-->
<div class="header-area" id="headerArea">
    <div class="container h-100 d-flex align-items-center justify-content-between">
      <!-- Back Button-->
      <div class="back-button"><a href="{{-- $redirection ?? url('ug-pg') --}}#" onclick="history.back()"><i class="lni lni-chevron-left"></i></a></div>
      <!-- Page Title-->
      <div class="page-heading">
        <h1 class="mb-0 h6">{{$title ?? "Pachavellam UG-PG "}}</h1>
      </div>
      <!-- Search Form-->
      <div class="search-form"></div>
    </div>
</div>

@endsection