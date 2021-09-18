@extends('layouts.app')

@section('body-styles') layout--halves @endsection('body-styles')

@section('content')
<div class="md:flex md:min-h-screen">
  <div class="md:w-1/2 bg-gradient flex items-center">
    <div class="w-full text-center p-12 md:p-32">
      <div class="lottie -ml-2 w-full"></div>
    </div>
  </div>
  <div class="md:w-1/2 bg-white p-8 md:p-24 lg:px-48 flex">
    <div class="self-center w-full ">
      <h1>{{ __('Reset Password') }}</h1>
      <form method="POST" action="{{ route('password.request') }}" aria-label="{{ __('Reset Password') }}">
          @csrf

          <input type="hidden" name="token" value="{{ $token }}">

          <div class="form-control form-control--simple">
              <input id="email" type="email" class="{{ $errors->has('email') ? ' error' : '' }}" name="email" value="{{ $email ?? old('email') }}" required autofocus>
              <label for="email">{{ __('E-Mail Address') }}</label>
              @if ($errors->has('email'))
                    @component('components.errormessage')
                      {{ $errors->first('email') }}
                    @endcomponent
              @endif
          </div>

          <div class="form-control form-control--simple">
              <input id="password" type="password" class="{{ $errors->has('password') ? ' error' : '' }}" name="password" required>
              <label for="password">{{ __('Password') }}</label>
              @if ($errors->has('password'))
                    @component('components.errormessage')
                      {{ $errors->first('password') }}
                    @endcomponent
              @endif
          </div>

          <div class="form-control form-control--simple">
              <input id="password-confirm" type="password" name="password_confirmation" required>
              <label for="password-confirm">{{ __('Confirm Password') }}</label>
          </div>

          <button type="submit" class="btn btn--md">
              {{ __('Reset Password') }}
          </button>

      </form>
    </div>
  </div>
</div>

@endsection
@section('endscripts')
<script src="/js/lottie/forgot-password/data.js" defer></script>
@endsection('endscripts')
