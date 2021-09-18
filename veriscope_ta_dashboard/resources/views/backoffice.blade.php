@extends('layouts.backoffice')

@section('content')
  <div class="flex text-center justify-center h-screen w-full">
    <div class="self-center">
      <h1>Shyft Backoffice v{{ $json['version']}}</h1>
      <p><a href="{{ route('login') }}">login</a></p>
    </div>
  </div>
@endsection('content')
