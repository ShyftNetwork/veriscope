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
          <strong>User Settings</strong><br>
          <a href="{{ route('account-settings') }}"><span class="hidden md:inline">Set Email/Password/2Fa</span><span class="inline md:hidden">Edit</span></a>
        </div>

        <div class="w-full bg-gray my-8 h-hairline"></div>
        <div class="flex-grow">
          <strong>Slack & Webhook Url</strong><br>
          <a href="{{ route('constants.index') }}"><span class="hidden md:inline">Manage Webhook</span><span class="inline md:hidden">Edit</span></a>
        </div>

        <div class="w-full bg-gray my-8 h-hairline"></div>
        <div class="flex-grow">
          <strong>API Tokens</strong><br>
          <a href="{{ route('token.index') }}"><span class="hidden md:inline">Manage Your API tokens</span><span class="inline md:hidden">Edit</span></a>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
