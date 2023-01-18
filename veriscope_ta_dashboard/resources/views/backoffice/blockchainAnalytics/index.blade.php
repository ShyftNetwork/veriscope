@extends('layouts.backoffice')
@section('body-styles')
constants
@endsection('body-styles')
@section('content')
<div class="zebra-sections">
  {{ Form::open(['route' => ['blockchain.analytics.update'], "method" => "put", "enctype" => "multipart/form-data", "class" => "form-horizontal"]) }}
  <div class="section">
    <div class="container py-12">
      <h1 class="px-8 md:px-12 md:pt-12 h2">{{ __('Blockchain Analytics Settings') }}</h1>

      <div class="md:flex">
        <div class="w-full p-8 pb-2 md:pt-2 md:pb-12 md:px-12">
          <p>These settings are applied to the site instantly so modify at your own risk.</p>

          @foreach($providers as $provider)
          <div class="flex flex-wrap">
            <div class="flex-grow text-sm pt-2"><h3>{{ $provider['name'] }}</h3><strong>API endpoint</strong>: POST {{ $provider['url'] }}</div>
            <div class="w-1/4 text-sm pl-8">
              {{ Form::check(1, $provider['id'] . '_enabled', 'Enabled', true, $provider['enabled']) }}
            </div>
            <div class="w-1/2 text-sm">
              <div class="form-control">
                <input placeholder='Api key' id="{{ $provider['name'] }}_key" type="text" name="{{ $provider['id']}}_key" value="{{ $provider['key'] }}" requried="" />
              </div>
              @if($provider['secret_key_exists'] == true)   
                <div class="form-control pt-2 mb-0">
                <input placeholder='Secret key' id="{{ $provider['name'] }}_secret_key" type="text" name="{{ $provider['id']}}_secret_key" value="{{ $provider['secret_key'] }}" requried="" />
                </div>
                @endif
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
        <a class="lg:mx-4" href="{{ url('/backoffice/blockchain-analytics-addresses') }}"><strong>Cancel</strong></a>
      </div>
    </div>
  </div>

  {{ Form::close() }}

</div>
@endsection
