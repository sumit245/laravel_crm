@extends('layouts.app')

@section('content')
    <div class="auth-form-light px-sm-5 px-4 py-5 text-left">
        <div class="brand-logo">
            <img src="https://www.sugslloyds.com/sugs-assets/logo.png" alt="logo">
        </div>
        <h4>Hello! Let's get started</h4>
        <h6 class="fw-light">Sign in to continue.</h6>
        <form class="pt-3" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <input id="email" type="email"
                    class="form-control form-control-lg @error('email') is-invalid @enderror" name="email"
                    value="{{ old('email') }}" placeholder="admin@example.com" required autocomplete="email" autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <div class="form-group position-relative">
                <input type="password" name="password" class="form-control form-control-lg" id="password"
                    placeholder="Password" autocomplete="current-password" required>
                <span class="position-absolute top-50 translate-middle-y end-0 me-3" style="cursor: pointer;"
                    onclick="togglePasswordVisibility()">
                    <i id="password-toggle-icon" class="mdi mdi-eye"></i>
                </span>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{omm. $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="form-group position-relative">
                <input type="checkbox" name="remember" class="form-check-input" id="remember"
                    {{ old('remember') ? 'checked' : '' }}
                    aria-label="Keep me signed in"
                    aria-describedby="remember-description">
                <label class="form-check-label" for="remember" id="remember-description">
                    Keep me signed in
                </label>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                    SIGN IN
                </button>
            </div>
            <div class="text-center mt-4 font-weight-light">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-primary">Forgot password?</a>
                @endif
            </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('mdi-eye');
                toggleIcon.classList.add('mdi-eye-off');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('mdi-eye-off');
                toggleIcon.classList.add('mdi-eye');
            }
        }
    </script>
@endsection
