@extends('layouts.app')
@section('body-styles')
settings
@endsection('body-styles')
@section('content')
<div class="md:flex min-h-screen-nav">
  <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
    <div class="self-center max-w-xs">
      <h1 class="h2">{{ __('Manage Your Account Settings') }}</h1>
      <p>If you need to make changes to your account information, you can update it here.</p>
    </div>
  </div>
  <div class="px-6 py-16 bg-boo flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-xs">
      {{ Form::open(['route' => ['email.manage.update'], "method" => "put"]) }}

        <h2 class="h2">{{ __('Change Your Email') }}</h2>

        {{ Form::simpleInput('email', 'email', 'E-Mail Address', old('email')) }}

        {{ Form::simpleInput('email', 'confirm_email', 'Confirm E-Mail Address', old('confirm_email')) }}

        <button type="submit" class="btn btn--md mt-8 mb-6 md:mb-0">
            {{ __('Submit') }}
        </button>
        &nbsp;&nbsp;&nbsp;<a href="{{ route('settings') }}">Cancel</a>

      {{ Form::close() }}
    </div>
  </div>
</div>
@endsection
