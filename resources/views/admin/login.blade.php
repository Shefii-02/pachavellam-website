<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Admin Login</title>
    <link rel="icon" href="{{ url('assets/images/favicon.png') }}" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm border-0" style="width:100%;max-width:400px">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="{{ url('images/logo-2.png') }}" alt="" style="max-height:48px" onerror="this.style.display='none'">
                <h4 class="mt-3 mb-0">Admin Login</h4>
                <small class="text-muted">Admin and Admin Staff only</small>
            </div>

            <form method="POST" action="{{ route('admin.login.submit') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-primary w-100">Log in</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
