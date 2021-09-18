@extends('errors/layout')

@section('content')

<div class="md:flex min-h-screen-nav">
  <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
    <div class="self-center max-w-xs">
      <h1 class="h2">{{ $exception->getMessage() }}</h1>
    </div>
  </div>
  <div class="px-6 py-16 bg-boo flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-sm">
      <img src="/images/errors/403-Unauthorized.svg">
    </div>
  </div>
</div>

@endsection
