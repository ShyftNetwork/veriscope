@extends('layouts.backoffice')
@section('body-styles')
attestation
@endsection('body-styles')
@section('content')

@section('content')
  <div class="container p-6 py-16 md:pt-48 md:pb-12">
    <div class="flex flex-wrap w-full md:mb-12">
        <div class="w-1/2 text-left">
          <h1 class="h2">
          Blockchain Analytics Report
          </h1>
        </div>
    </div>
    <good-table url="get-blockchain-analytics-report"
          filter="reportId|{{ $report->id }}"
  :columns="[
        {
          label: 'Field',
          field: 'field',
        },
        {
          label: 'Data',
          field: 'data',
          html: true,
          sortable: false,
        }
      ]"
      :hideSearch=true
  ></good-table>

    <br><br>

  </div>
  @include('partials.footer')

@endsection('content')
