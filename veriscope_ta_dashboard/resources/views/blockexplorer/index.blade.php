@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          All Attestations
          </h1>
        </div>
    </div>
    <good-table url="shyft-smart-contract-events"
    ref="attestationsTable"
    :columns="[
      {
        label: 'ID',
        field: 'id',
      },
      {
        label: 'Created At',
        field: 'created_at',
      },
      {
        label: 'Block number',
        field: 'block_number',
        html: false,
        sortable: true,
      },
      {
        label: 'Transaction Hash',
        field: 'transaction_hash',
      },
      {
        label: 'Attestation Hash',
        field: 'attestation_hash',
      },
      {
        label: 'User Account',
        field: 'user-account',
        html: true,
        sortable: false,
      },
      {
        label: 'TA Account',
        field: 'ta-account',
        html: true,
        sortable: false,
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
