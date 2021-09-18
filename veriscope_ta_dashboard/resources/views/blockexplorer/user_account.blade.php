@extends('layouts.backoffice')
@section('body-styles')
user_account_address
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          User Attestations
          </h1>
        </div>
    </div>
    <good-table url="get-user-account-attestations"
          filter="userAccount|{{ $user_account_address }}"
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
        label: 'TA Account',
        field: 'ta_account',
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
