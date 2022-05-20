@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Blockchain Analytics Reports
          </h1>
        </div>
    </div>
    <a href="blockchain-analytics">
      <button class="btn">
        Blockchain Analytics API Settings
      </button>
    </a>
    <a href="blockchain-analytics-addresses/new-report">
      <button class="btn">
        Create Blockchain Analytics Report
      </button>
    </a>
    <good-table url="blockchain-analytics-addresses"
    :columns="[
      {
        label: 'id',
        field: 'id',
      },
      {
        label: 'Crypto Address',
        field: 'wallet_address',
      },
      {
        label: 'Blockchain',
        field: 'blockchain',
      },
      {
        label: 'Provider',
        field: 'provider.name',
      },
      {
        label: 'Custodian',
        field: 'custodian',
      },
      {
        label: 'Status',
        field: 'response_status_code',
      },
      {
        label: 'View',
        field: 'action',
        html: true,
        sortable: false,
      }
    ]"
    :hideSearch=false
    ></good-table>

  </div>

  @include('partials.footer')

@endsection('content')
