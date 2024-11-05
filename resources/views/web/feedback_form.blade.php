@extends('web.layout')

@section('styles')
<style>
   

.rating input {
  display: none;
}

.rating label {
  cursor: pointer;
  width: 30px;
  height: 30px;
    background-image: url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L9.53 8.45L2 9.27L7.27 14.18L5.82 21L12 17.77L18.18 21L16.73 14.18L22 9.27L14.47 8.45L12 2Z"/></svg>');

  background-size: cover;
}

.rating input:checked ~ label,
.rating input:checked ~ label ~ label {
  background-image: url('data:image/svg+xml;charset=utf-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2L9.53 8.45L2 9.27L7.27 14.18L5.82 21L12 17.77L18.18 21L16.73 14.18L22 9.27L14.47 8.45L12 2Z" fill="currentColor"/></svg>');
}

</style>
@endsection


@section('content')
<!-- **************** MAIN CONTENT START **************** -->
<main>
	<section class="p-0 d-flex align-items-center position-relative overflow-hidden">
	
		<div class="container-fluid mt-4">

            <div class="container">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if($message == '0')
                <div class="row justify-content-center">
              <div class="col-md-6">
                <div class="card shadow">
                  <div class="card-body">
                    <h1 class="card-title text-center text-capitalize">@if(isset($feed)) {{$feed->title}} @else Feedback Form @endif </h1>
                    <p class="mt-2 mb-3 text-center text-capitalize">
                        @if(isset($feed)) {{$feed->description}} @else  @endif
                    </p>
                    <form action="" method="POST">
                        @csrf()
                      <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" required id="name" name="name" autocomplete="off" class="form-control">
                      </div>
                      <div class="mb-3">
                        <label for="mobile" class="form-label">Mobile</label>
                        <input type="number"  autocomplete="off" name="mobile" required id="mobile" class="form-control">
                      </div>
                      <div class="mb-3">
                        <label for="message" class="form-label">Feedback</label>
                        <textarea id="message" name="message" class="form-control"></textarea>
                      </div>
                      <div class="mb-3">
                        <label for="rating" class="form-label">Rating</label>
                
                        <div class="rating" style="display: flex;flex-direction: row-reverse;justify-content: space-evenly;">
                          <input type="radio" id="star1" name="rating" value="5" />
                          <label for="star1"><i class="bi bi-star"></i></label>
                          <input type="radio" id="star2" name="rating" value="4" />
                          <label for="star2"><i class="bi bi-star"></i></label>
                          <input type="radio" id="star3" name="rating" value="3" />
                          <label for="star3"><i class="bi bi-star"></i></label>
                          <input type="radio" id="star4" name="rating" value="2" />
                          <label for="star4"><i class="bi bi-star"></i></label>
                          <input type="radio" id="star5" name="rating" value="1" />
                          <label for="star5"><i class="bi bi-star"></i></label>
                        </div>
                      </div>
                      <div class="text-center">
                        <button type="submit" class="btn btn-primary">Submit</button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
                @else
                    <div class="alert alert-danger">
                        {{ $message  }}
                    </div>
                @endif
            </div>

		</div>
	</section>
</main>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
  $('.rating input').change(function() {
    var selectedRating = $(this).val();
    $('.rating label.star').removeClass('star-filled');
    $('.rating label.star').each(function(index) {
      if (index >= selectedRating - 1) {
        $(this).addClass('star-filled');
      }
    });
  });
});



</script>
@endsection