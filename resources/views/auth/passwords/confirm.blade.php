@extends('layouts.app')

@section('content')
    <div class="auth-form-light px-sm-5 px-4 py-5 text-left">
        <div class="brand-logo">
            <img src="https://www.sugslloyds.com/sugs-assets/logo.png" alt="logo">
        </div>
        <h4>{{ __('Confirm Password') }}</h4>
        <h6 class="fw-light">{{ __('Please confirm your password before continuing.') }}</h6>

        <form class="pt-3" method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="form-group position-relative">
                <input id="password" type="password"
                    class="form-control form-control-lg @error('password') is-invalid @enderror" name="password"
                    placeholder="Password" required autocomplete="current-password">
                <span class="position-absolute top-50 translate-middle-y end-0 me-3" style="cursor: pointer;"
                    onclick="togglePasswordVisibility('password')">
                    <i id="password-toggle-icon" class="mdi mdi-eye"></i>
                </span>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                    {{ __('Confirm Password') }}
                </button>
            </div>

            <div class="text-center mt-4 font-weight-light">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-primary">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <script>
        function togglePasswordVisibility(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = document.getElementById(fieldId + '-toggle-icon');

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
