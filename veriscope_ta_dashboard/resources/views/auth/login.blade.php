@extends('layouts.app')

@section('body-styles') layout--halves layout--halves--request @endsection('body-styles')

@section('content')
<div class="md:flex md:min-h-screen">
  <div class="px-6 pt-20 pb-16 bg-gradient flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-sm xxl:max-w-md">
      <div class="lottie -ml-2 w-full"></div>
    </div>
  </div>
  <div class="px-6 py-16 bg-white flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-xs">
      <h1 class="h2">{{ __('Sign In to SCOPE') }}</h1>
      <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}" autocomplete="off" class="mt-6" novalidate>
          @csrf

          {{ Form::simpleInput('email', 'email', 'E-Mail Address', old('email')) }}

          {{ Form::simpleInput('password', 'password', 'Password', old('password')) }}

          <button type="submit" class="btn btn--md w-full mt-4">
              {{ __('Login') }}
          </button>

          <div class="md:flex items-center w-full mt-12">
              <div class="md:flex-grow">
                {{ Form::check(1, 'remember', __('Remember Me'), 1, old('remember'), false) }}
              </div>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section('endscripts')
<script src="/js/lottie/login/data.js" defer></script>
@endsection('endscripts')
