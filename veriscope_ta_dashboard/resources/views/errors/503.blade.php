@extends('errors.layout')

@section('title', 'Service Unavailable')

@section('content')
<div class="md:flex min-h-screen-nav">
  <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
    <div class="self-center max-w-xs">
      <h1 class="h2">We’re currently working on the site.</h1>
      <p>It will be up and running again shortly. In the mean time, you can learn more about the Shyft network at our website:</p>
      <a href="https://www.shyft.network" target="_blank" class="btn">Learn More About Shyft</a>
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
<script src="/js/lottie/maintenance-mode/data.js" defer></script>
@endsection('endscripts')
