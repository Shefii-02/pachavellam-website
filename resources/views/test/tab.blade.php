@extends('layouts.pain-layout')

@section('content')
<div class="container-fluid min-vh-100 d-flex align-items-center bg-light">
    <div class="row justify-content-center g-4">
        <!-- Card 1: Competitive Exams -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 25px;">
                <img src="{{ asset('logo/pachavellam.png') }}" class="card-img-top px-5 pt-3" alt="Competitive Exams" style="height: 100px; object-fit: contain;">
                <div class="card-body text-center">
                    <h5 class="card-title text-dark fw-bold">For Competitive Exams</h5>
                    <p class="card-text small">
                        Prepare to excel in Kerala PSC and other competitive exams with expert guidance and comprehensive study materials.
                    </p>
                    <a href="{{ url('/kpsc') }}" class="btn btn-outline-dark">Learn More</a>
                </div>
            </div>
        </div>
        <!-- Card 2: School Tuition -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 25px;">
                <img src="{{ asset('logo/schooly.png') }}" class="card-img-top px-5 pt-3" alt="School Tuition" style="height: 100px; object-fit: contain;">
                <div class="card-body text-center">
                    <h5 class="card-title text-dark fw-bold">For School Tuition</h5>
                    <p class="card-text small">
                        From primary to graduation, our experienced tutors provide personalized learning solutions to boost academic performance.
                    </p>
                    <a href="https://www.schooly.pachavellam.com" class="btn btn-outline-dark">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
