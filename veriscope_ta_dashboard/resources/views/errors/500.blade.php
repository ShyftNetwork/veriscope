@extends('errors.layout')
@section('title', 'Server Error')
@section('content')
<div class="md:flex min-h-screen-nav">
  <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
    <div class="self-center max-w-xs">
      <h1 class="h2">We are currently experiencing server issues</h1>
      <p>Hit refresh and try again</p>
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
<script src="/js/lottie/500/data.js" defer></script>
@endsection('endscripts')
