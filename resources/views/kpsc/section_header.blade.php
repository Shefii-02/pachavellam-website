@section('header-tab')
<!-- Header Area-->
<div class="header-area" id="headerArea">
    <div class="container h-100 d-flex align-items-center justify-content-between">
      <!-- Back Button-->
      <div class="back-button"><a href="{{-- $redirection ?? url()->previous() --}}#" onclick="history.back()"><i class="lni lni-chevron-left"></i></a></div>
      <!-- Page Title-->
      <div class="page-heading">
        <h6 class="mb-0">{{$title}}</h6>
      </div>
      <button type="button" class="pv-theme-btn ms-auto me-2" data-theme-toggle aria-label="Switch light or dark mode"><i class="bi bi-moon-stars-fill"></i><i class="bi bi-sun-fill"></i></button>
      <!-- Search Form-->
      <div class="search-form">
          <!-- Navbar Toggler-->
      <div class="navbar--toggler" id="newstenNavbarToggler">
          <span></span>
          <span></span>
          <span></span>
          <span></span>
      </div>
      </div>
    </div>
</div>

  @include('kpsc.partials.drawer')
@endsection