@extends('layouts.web-layout')
@section('content')
@php
    $photo = $user->image ? asset('storage/users/' . $user->image) : null;
    $initial = strtoupper(mb_substr($user->name ?: 'A', 0, 1));
@endphp

<nav class="navbar navbar-light border-bottom py-2 mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ url('admin') }}"><i class="bi bi-arrow-left me-2"></i>Back to dashboard</a>
        <span class="text-muted small">{{ $user->type }}</span>
    </div>
</nav>

<div class="container pb-5" style="max-width: 920px">
    <h2 class="h4 mb-3">My profile</h2>

    <div class="row g-4">
        {{-- profile --}}
        <div class="col-lg-7">
            <div class="card border">
                <div class="card-header border-bottom"><h5 class="mb-0">Profile details</h5></div>
                <div class="card-body">
                    <form action="{{ url('admin/profile') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="d-flex align-items-center mb-4">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="" class="rounded-circle me-3" style="width:72px;height:72px;object-fit:cover">
                            @else
                                <img src="{{ asset('assets/images/user.png') }}" alt="" class="rounded-circle me-3" style="width:72px;height:72px;object-fit:cover">
                            @endif
                            <div class="flex-grow-1">
                                <label class="form-label mb-1">Profile photo</label>
                                <input type="file" name="photo" accept="image/*" class="form-control form-control-sm">
                                <div class="form-text">JPG, PNG or WEBP, max 2 MB.</div>
                                @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
                                @if ($user->image)
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="rp">
                                        <label class="form-check-label small" for="rp">Remove current photo</label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" required class="form-control" value="{{ old('name', $user->name) }}">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            @if ($canEditContact)
                                <input type="email" name="email" required class="form-control" value="{{ old('email', $user->email) }}">
                                @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                            @else
                                <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mobile
                                @if ($user->mobile)
                                    <span class="badge bg-{{ $user->mobile_verified_at ? 'success' : 'secondary' }} ms-1">{{ $user->mobile_verified_at ? 'verified' : 'not verified' }}</span>
                                @endif
                            </label>
                            @if ($canEditContact)
                                <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $user->mobile) }}" placeholder="e.g. 9876543210">
                                <div class="form-text">Changing the number marks it as not verified.</div>
                                @error('mobile')<div class="text-danger small">{{ $message }}</div>@enderror
                            @else
                                <input type="text" class="form-control" value="{{ $user->mobile ?: '—' }}" readonly disabled>
                            @endif
                        </div>

                        @unless ($canEditContact)
                            <div class="alert alert-info py-2 small mb-3"><i class="bi bi-lock me-1"></i>Email and mobile can only be changed by an Admin.</div>
                        @endunless

                        <button class="btn btn-primary mb-0"><i class="bi bi-check me-1"></i>Save profile</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- password --}}
        <div class="col-lg-5">
            <div class="card border">
                <div class="card-header border-bottom"><h5 class="mb-0">Change password</h5></div>
                <div class="card-body">
                    <form action="{{ url('admin/profile/password') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Current password</label>
                            <input type="password" name="current_password" required class="form-control" autocomplete="current-password">
                            @error('current_password')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New password</label>
                            <input type="password" name="password" required minlength="8" class="form-control" autocomplete="new-password">
                            <div class="form-text">At least 8 characters.</div>
                            @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm new password</label>
                            <input type="password" name="password_confirmation" required minlength="8" class="form-control" autocomplete="new-password">
                        </div>
                        <button class="btn btn-warning mb-0"><i class="bi bi-key me-1"></i>Change password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
