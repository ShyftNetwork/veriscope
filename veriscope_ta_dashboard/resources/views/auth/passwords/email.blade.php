@extends('layouts.app')

@section('body-styles') layout--halves @endsection('body-styles')

@section('content')
<div class="md:flex md:min-h-screen">
  <div class="px-6 pt-24 pb-16 bg-gradient flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-sm xxl:max-w-md">
      <div class="lottie -ml-2 w-full"></div>
    </div>
  </div>
  <div class="px-6 py-16 bg-white flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-xs">
        <h1 class="h2">{{ __('Forgot your password?') }}</h1>
        <p>Don’t worry! Enter the email you used to sign up below and we’ll email you with instructions on how to reset your password.</p>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" aria-label="{{ __('Reset Password') }}" novalidate>
                @csrf

                <div class="form-control form-control--simple">
                    <input id="email" type="email" class="{{ $errors->has('email') ? ' error' : '' }}" name="email" value="{{ old('email') }}" required>
                    <label for="email">{{ __('E-Mail Address') }}</label>
                    @if ($errors->has('email'))
                        @component('components.errormessage')
                            {{ $errors->first('email') }}
                        @endcomponent
                    @endif
                </div>

                <button type="submit" class="btn btn--md w-full">
                    {{ __('Send Password Reset Link') }}
                </button>
            </form>
        </div>
    </div>
  </div>
</div>
@endsection
@section('endscripts')
<script src="/js/lottie/forgot-password/data.js" defer></script>
@endsection('endscripts')
