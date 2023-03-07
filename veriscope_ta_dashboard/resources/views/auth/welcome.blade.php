@extends('layouts.app')

@section('content')
<div class="md:flex min-h-screen-nav">
  <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
    <div class="self-center max-w-xs">
      <h1 class="h2">{{ __('Welcome to Veriscope') }}, {{ Auth::user()->first_name }}!</h1>
      <a href="{{ route('manage-organization') }}" class="btn btn--lg mt-12">VASP Profile</a>
    </div>
  </div>
</div>
@endsection
