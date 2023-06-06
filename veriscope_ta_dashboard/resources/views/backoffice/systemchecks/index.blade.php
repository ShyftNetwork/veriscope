@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

<div class="container p-6 py-16 md:pt-48 md:pb-12">
  <div class="flex flex-wrap w-full md:mb-12">
      <div class="w-1/2 text-left">
        <h1 class="h2">
        System Checks
        </h1>
      </div>
  </div>
  <good-table url="systemchecks"
  :columns="[
    {
      label: 'Name',
      field: 'name',
    },
    {
      label: 'Running',
      field: 'running',
      html: true
    },
    {
      label: 'Message',
      field: 'message',
    }
  ]"
  :haspagination=false
  :hideSearch=true
  ></good-table>

  <br><br>

</div>
@endsection('content')
