@extends('layouts.backoffice')
@section('body-styles')
account_address
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Address
          </h1>
        </div>
    </div>
    <good-table url="get-smart-contract-address-transactions"
          filter="address|{{ $account_address }}"
  :columns="[
    {
      label: 'ID',
      field: 'id',
    },
    {
      label: 'Transaction Hash',
      field: 'transaction_hash',
    },
    {
      label: 'Created At',
      field: 'created_at',
    },
    {
      label: 'Nonce',
      field: 'nonce'
    },
    {
      label: 'Block Hash',
      field: 'block_hash'
    },
    {
      label: 'Block Number',
      field: 'block_number'
    },
    {
      label: 'Transaction Index',
      field: 'transaction_index'
    },
    {
      label: 'From Address',
      field: 'from_address'
    },
    {
      label: 'To Address',
      field: 'to_address'
    },
    {
      label: 'Value',
      field: 'value'
    },
    {
      label: 'Gas',
      field: 'gas'
    },
    {
      label: 'Gas Price',
      field: 'gas_price'
    },
    {
      label: 'Payload',
      field: 'payload'
    }
  ]"
  ></good-table>

    <br><br>

  </div>
  @include('partials.footer')

@endsection('content')
