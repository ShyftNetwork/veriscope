@extends('layouts.backoffice')
@section('body-styles')
id
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Validations
          </h1>
        </div>
    </div>
    <good-table url="trust-anchor-extra-data-unique-validations"
          filter="{{$id}}"

      :columns="[
        {
          label: 'Trust Anchor Address',
          field: 'trust_anchor_address',
        },
        {
          label: 'Key Name',
          field: 'key_value_pair_name',
        },
        {
          label: 'Key Value',
          field: 'key_value_pair_value',
        },
        {
          label: 'Validator Address',
          field: 'validator_address',
        },
        {
          label: 'Transaction Hash',
          field: 'transaction_hash',
        }
      ]"
      :hideSearch=true
     ></good-table>

    <br><br>

  </div>
  @include('partials.footer')

@endsection('content')
