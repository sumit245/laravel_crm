@extends('layouts.app')

@section('content')
    <div class="auth-form-light px-sm-5 px-4 py-5 text-left">
        <div class="brand-logo">
            <img src="https://www.sugslloyds.com/sugs-assets/logo.png" alt="logo">
        </div>
        <h4>Reset Password</h4>
        <h6 class="fw-light">Enter your email to receive password reset link.</h6>

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <form class="pt-3" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="form-group">
                <input id="email" type="email"
                    class="form-control form-control-lg @error('email') is-invalid @enderror" name="email"
                    value="{{ old('email') }}" placeholder="Email Address" required autocomplete="email" autofocus>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
                    Send Password Reset Link
                </button>
            </div>

            <div class="text-center mt-4 font-weight-light">
                <a href="{{ route('login') }}" class="text-primary">Back to Login</a>
            </div>
        </form>
    </div>
@endsection
