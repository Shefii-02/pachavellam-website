@extends('layouts.web-layout')

@section('content')
<div class="container">
    <form action="" method="POST" class="mt-5">
        @csrf()
        <div class="mb-3">
            <label for="year" class="form-label">Year</label>
            <input type="text" value="{{ old('year') }}" class="form-control" id="year" name="year" aria-describedby="emailHelp">
        </div>
        <div class="mb-3">
            <label for="month" class="form-label">Month</label>
            <select class="form-control" id="month" name="month">
                <option>January</option>
                <option>February</option>
                <option>March</option>
                <option>April</option>
                <option>May</option>
                <option>June</option>
                <option>July</option>
                <option>August</option>
                <option>September</option>
                <option>October</option>
                <option>November</option>
                <option>December</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="day" class="form-label">Day</label>
            <input type="text" class="form-control" id="day" name="day">
                
        </div>
        <div class="mb-3">
            <label for="url" class="form-label">Url</label>
            <input type="text" value="{{ old('url') }}" class="form-control" id="url" name="url">
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    
</div>

@endsection
