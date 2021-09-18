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
      <h1 class="h2">{{ __('Request an Invite') }}</h1>
      <p>SCOPE accounts are currently granted on an invite-only basis. Please request an invitation below in order to access the SCOPE Portal.</p>

      <form method="POST" action="{{ route('send-access-request') }}" aria-label="{{ __('Request') }}" autocomplete="off" novalidate>
          @csrf

          {{ Form::hidden('hp') }}

          {{ Form::simpleInput('text', 'first_name', 'First Name', old('first_name')) }}

          {{ Form::simpleInput('text', 'last_name', 'Last Name', old('last_name')) }}

          {{ Form::simpleInput('email', 'email', 'E-Mail Address', old('email')) }}

          <div class="mt-16">
            <button type="submit" class="btn btn--md w-full">
                {{ __('Send My Request') }}
            </button>
          </div>
      </form>
  </div>
</div>
</div>
@endsection
@section('endscripts')
<script src="/js/lottie/request-invite/data.js" defer></script>
@endsection('endscripts')
