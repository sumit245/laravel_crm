@extends('layouts.app')

@section('content')
    <div class="auth-form-light px-sm-5 px-4 py-5 text-left">
        <div class="brand-logo">
            <img src="https://www.sugslloyds.com/sugs-assets/logo.png" alt="logo">
        </div>
        <h4>{{ __('Verify Your Email Address') }}</h4>
        <h6 class="fw-light">{{ __('Please verify your email before continuing.') }}</h6>

                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

        <div class="mt-3">
            <p>{{ __('Before proceeding, please check your email for a verification link.') }}</p>
            <p>{{ __('If you did not receive the email') }},</p>
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                <button type="submit" class="btn btn-link p-0 m-0 align-baseline text-primary">
                    {{ __('click here to request another') }}
                </button>.
                    </form>
                </div>

        <div class="text-center mt-4 font-weight-light">
            <a href="{{ route('login') }}" class="text-primary">Back to Login</a>
    </div>
</div>
@endsection
