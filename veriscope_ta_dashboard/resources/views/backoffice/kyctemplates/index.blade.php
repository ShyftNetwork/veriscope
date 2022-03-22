@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

<div class="container p-6 py-16 md:pt-48 md:pb-12">
  <div class="flex flex-wrap w-full md:mb-12">
      <div class="w-1/2 text-left">
        <h1 class="h2">
        KYC Templates
        </h1>
      </div>
  </div>
  <good-table url="kyctemplates"
  :columns="[
    {
        label: 'View',
        field: 'action',
        html: true,
        sortable: false,
    },
    {
      label: 'ID',
      field: 'id',
    },
    {
      label: 'Attestation Hash',
      field: 'attestation_hash',
    },
    {
      label: 'Beneficiary TA Address',
      field: 'beneficiary_ta_address',
    },
    {
      label: 'Beneficiary User Address',
      field: 'beneficiary_user_address',
    },
    {
      label: 'Coin Blockchain',
      field: 'coin_blockchain'
    },
    {
      label: 'Coin Token',
      field: 'coin_token'
    },
    {
      label: 'Coin Address',
      field: 'coin_address'
    },
    {
      label: 'Coin Memo',
      field: 'coin_memo'
    },
    {
      label: 'Sender TA Address',
      field: 'sender_ta_address',
    },
    {
      label: 'Sender User Address',
      field: 'sender_user_address',
    }
  ]"
  :hideSearch=false
  ></good-table>

  <br><br>

</div>
@endsection('content')
