@extends('layouts.backoffice')
@section('body-styles')
constants
@endsection('body-styles')
@section('content')
<div class="zebra-sections">
  {{ Form::open(['route' => ['constants.update'], "method" => "put", "enctype" => "multipart/form-data", "class" => "form-horizontal"]) }}
  <div class="section">
    <div class="container py-12">
      <h1 class="px-8 md:px-12 md:pt-12 h2">{{ __('Portal Settings') }}</h1>

      <div class="md:flex">
        <div class="md:w-1/2 p-8 pb-2 md:pt-2 md:pb-12 md:px-12">
          <p>These settings are applied to the site instantly so modify at your own risk.</p>

          @foreach($constants as $constant)
          <div class="flex flex-wrap">
            <div class="flex-grow md:w-1/2 text-sm pt-2"><strong>{{ $constant->description }}:</strong></div>
            <div class="w-full md:w-1/2 text-sm pl-8">
              @if($constant->type == 'boolean')
              {{ Form::check(1, $constant->name, 'Enabled', true, $constant->value) }}
              @elseif($constant->type == 'text')
              <div class="form-control pt-0 mb-0">
                <input id="{{ $constant->name }}" type="text" name="{{ $constant->name }}" value="{{ $constant->value }}" requried="" />
              </div>
              @elseif($constant->type == 'select')
              {{ Form::select($constant->name, array_combine(explode(',', $constant->options), explode(',', $constant->options)), $constant->value) }}
              @endif()
            </div>
            <div class="my-4 border-b border-hairline w-full"></div>
          </div>
          @endforeach()
        </div>
      </div>
    </div>
  </div>

  <div class="section">
    <div class="container py-12">
      <div class="flex flex-wrap items-center">
        <div class="w-full lg:w-1/4 my-8 lg:my-12 lg:mx-4">
          <button class="btn btn-primary" type="submit"><i class="fa fa-btn fa-user"></i> Update</button>
        </div>
        <a class="lg:mx-4" href="{{ url('/backoffice/users') }}"><strong>Cancel</strong></a>
      </div>
    </div>
  </div>

  {{ Form::close() }}

</div>
@endsection
