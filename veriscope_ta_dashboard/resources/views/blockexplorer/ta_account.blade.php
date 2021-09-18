@extends('layouts.backoffice')
@section('body-styles')
trustanchor_address
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Trust Anchor Attestations
          </h1>
        </div>
    </div>
    <good-table url="get-ta-account-attestations"
          filter="trustAnchorAccount|{{ $trustanchor_address }}"
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
        label: 'Jurisdiction',
        field: 'jurisdiction',
      },
      {
        label: 'User Account',
        field: 'user_account',
      },
      {
        label: 'Effective Time',
        field: 'effective_time',
      },
      {
        label: 'Expiry Time',
        field: 'expiry_time',
      },
      {
        label: 'Attestation Hash',
        field: 'attestation_hash',
      }

    ]"
      :hideSearch=true
  ></good-table>

    <br><br>

  </div>
  @include('partials.footer')

@endsection('content')
