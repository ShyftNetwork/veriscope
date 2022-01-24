@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Verified Trust Anchors
          </h1>
        </div>
    </div>
    <simple-button :on-click=callRefreshAllVerifiedTAs>
        Refresh All Verified Trust Anchors
    </simple-button>
    <good-table url="verified-trust-anchors"
    ref="verifiedTATable"
    :columns="[
      {
        label: 'Account Address',
        field: 'account_address',
      }
    ]"
    :hideSearch=false
    ></good-table>

  </div>

  @include('partials.footer')

@endsection('content')
