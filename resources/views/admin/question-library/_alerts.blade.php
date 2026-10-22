@if (session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif
@if ($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
