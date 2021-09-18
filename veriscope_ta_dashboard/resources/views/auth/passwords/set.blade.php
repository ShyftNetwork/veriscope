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
      <h1>{{ __('Get Started') }}</h1>
      <p>Welcome to the Shyft Network! We’re excited to grant you access to the early beta release of our platform.</p>
      <form method="POST" action="{{ route('password.assign') }}" aria-label="{{ __('Get Started') }}" novalidate>
          @csrf

          <input type="hidden" name="token" value="{{ $token }}">

          <div class="form-control form-control--simple">
              <input id="email" type="email" class="{{ $errors->has('email') ? ' error' : '' }}" name="email" value="{{ $user->email }}" required autofocus>
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

          @if ($errors->has('legal_agree'))
                @component('components.errormessage')
                  {{ $errors->first('legal_agree') }}
                @endcomponent
          @endif

          {{ Form::check(1, 'legal_agree', 'I have read and I agree to the Shyft Network Inc. <a href="https://www.shyft.network/privacy-policy" target="_blank">Privacy Policy</a> and <a href="https://www.shyft.network/terms-and-conditions" target="_blank">Terms and Conditions</a>.', 1, old('legal_agree'), false) }}

          {{ Form::check(1, 'marketing_subscribe', __('Add me to the Shyft mailing list so I can receive updates and announcements'), 1, 0, false) }}

          <button type="submit" class="btn btn--md mt-8">
              {{ __('Create My Account') }}
          </button>

      </form>
    </div>
  </div>
</div>

@endsection
@section('endscripts')
<script src="/js/lottie/login/data.js" defer></script>
@endsection('endscripts')
