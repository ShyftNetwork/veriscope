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
    <div class="self-center w-full max-w-sm">
      <div class="flex flex-wrap">
        <div class="flex-grow">
          <strong>Your Email Address:</strong><br>
          {{ Auth::user()->email }}
        </div>
        <div class="text-right">
          <a href="{{ route('email.manage') }}"><span class="hidden md:inline">Change My Email</span><span class="inline md:hidden">Edit</span></a>
        </div>
        <div class="w-full bg-gray my-8 h-hairline"></div>
        <div class="flex-grow">
          <strong>Your Password:</strong><br>
          ••••••••••••••••
        </div>
        <div class="text-right">
          <a href="{{ route('password.manage') }}"><span class="hidden md:inline">Update My Password</span><span class="inline md:hidden">Edit</span></a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
