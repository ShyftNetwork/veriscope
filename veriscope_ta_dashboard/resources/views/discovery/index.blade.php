@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Discovery Layer Key Value Pairs
          </h1>
        </div>
    </div>
    <good-table  url="trust-anchor-extra-data-unique"
    ref="discoveryLayerTable"
    :columns="[
      {
        label: 'Key Name',
        field: 'key_value_pair_name',
      },
      {
        label: 'Key Value',
        field: 'key_value_pair_value',
      },
      {
        label: 'Block Number',
        field: 'block_number',
      },
      {
        label: 'Trust Anchor Address',
        field: 'trust_anchor_address',
      },
      {
        label: 'Validation',
        field: 'action',
        html: true,
        sortable: false,
      },
      {
        label: 'Transaction Hash',
        field: 'transaction_hash',
      }
    ]"
    :hideSearch=false
    ></good-table>

  </div>

  @include('partials.footer')

@endsection('content')
