@extends('layouts.app')

@section('body-styles') layout--halves @endsection('body-styles')

@section('content')
<div class="md:flex min-h-screen">
  <div class="px-6 py-20 bg-gradient flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-sm xxl:max-w-md">
      <img src="/images/auth/RequestInvite@2x.png" class="-ml-2">
    </div>
  </div>
  <div class="px-6 py-16 bg-white flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-xs">
      <h1>{{ __('Request Sent') }}</h1>
      <p>You will receive an email with sign in instructions shortly. Once you’ve created your account you will gain access to our portal.</p>
      <p>In the meantime, please visit our website for more information.</p>

      <a href="https://shyft.network" class="btn btn--md w-full mt-12">Visit Shyft Website</a>

  </div>
</div>
</div>
@endsection
