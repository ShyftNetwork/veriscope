@extends('layouts.backoffice')
@section('body-styles')
tokens
@endsection('body-styles')
@section('content')

<div class="container p-6 py-16 md:pt-48 md:pb-12">
  <div class="flex flex-wrap w-full md:mb-12">
      <div class="w-1/2 text-left">
        <h1 class="h2">
          API Tokens
        </h1>
        <a href="{{route('token.create')}}" class="btn btn-primary">Create Token</a>

      </div>
  </div>
  <good-table url="tokens"
  :columns="[
    {
      label: 'ID',
      field: 'id'
    },
    {
      label: 'Created At',
      field: 'created_at'
    },
    {
      label: 'Expired At',
      field: 'expires_at',
    },
    {
        label: 'Show',
        field: 'show',
        html: true,
        sortable: false,
    },
    {
        label: 'Revoke',
        field: 'revoke',
        html: true,
        sortable: false,
    },
  ]"
  :hideSearch=true
  ></good-table>

  <br><br>

</div>

@endsection('content')
