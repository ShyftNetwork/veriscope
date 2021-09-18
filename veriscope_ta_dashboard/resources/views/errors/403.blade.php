@extends('errors.layout')

@section('title', 'Unauthorized')

@section('content')
<div class="md:flex min-h-screen-nav">
  <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
    <div class="self-center max-w-xs">
      <h1 class="h2">You do not have permissions to access content</h1>
    </div>
  </div>
  <div class="px-6 py-16 bg-boo flex justify-center md:w-1/2">
    <div class="self-center w-full max-w-sm">
      <div class="lottie -ml-2 w-full"></div>
    </div>
  </div>
</div>
@endsection()
@section('endscripts')
<script src="/js/lottie/403/data.js" defer></script>
@endsection('endscripts')
