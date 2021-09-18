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
      {{ Form::open(['route' => ['password.manage.update'], "method" => "put", "novalidate" => "novalidate"]) }}

        <h2 class="h2">Update Your Password</h2>

        {{ Form::simpleInput('password', 'old_password', 'Enter Your Old Password', old('old_password')) }}

        {{ Form::simpleInput('password', 'password', 'Enter Your New Password', old('password')) }}

        {{ Form::simpleInput('password', 'password_confirmation', 'Confirm Your New Password', old('password_confirmation')) }}

        <button type="submit" class="btn btn--md mt-8 mb-6 md:mb-0">
            {{ __('Submit') }}
        </button>
        &nbsp;&nbsp;&nbsp;<a href="{{ route('settings') }}">Cancel</a>

      {{ Form::close() }}
    </div>
  </div>
</div>
@endsection
