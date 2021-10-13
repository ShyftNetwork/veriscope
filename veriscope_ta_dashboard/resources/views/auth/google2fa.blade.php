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
      <h1 class="h2">{{ __('Two Factor Authentication') }}</h1>
      <p>Two factor authentication (2FA) strengthens access security by requiring two methods (also referred to as factors) to verify your identity. Two factor authentication protects against phishing, social engineering and password brute force attacks and secures your logins from attackers exploiting weak or stolen credentials.</p>

      @if (session('error'))
          <div class="alert alert-danger">
              {{ session('error') }}
          </div>
      @endif
      @if (session('success'))
          <div class="alert alert-success">
              {{ session('success') }}
          </div>
      @endif

         <strong>Enter the pin from Google Authenticator</strong><br/><br/>
      <form class="form-horizontal" action="{{ route('2faVerify') }}" method="POST">
         {{ csrf_field() }}
         <div class="form-group{{ $errors->has('one_time_password-code') ? ' has-error' : '' }}">
            {{ Form::simpleInput('text', 'one_time_password', 'One Time Password', old('email')) }}
         </div>
         <div class="form-group">
             <div class="col-md-6 col-md-offset-4">
                  <button class="btn btn-primary" type="submit">Authenticate</button>
             </div>
         </div>
      </form>

      </div>
    </div>
  </div>
</div>
@endsection
@section('endscripts')
<script src="/js/lottie/login/data.js" defer></script>
@endsection('endscripts')
