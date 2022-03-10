@extends('layouts.backoffice')
@section('body-styles')
@endsection('body-styles')
@section('content')

@section('content')
<div class="zebra-sections">
  <div class="section">
    <div class="container py-12">
      <h1 class="px-8 md:px-12 md:pt-12 h2">Create Blockchain Analytics Report</h1>
    </div>
    <div class="section">
      <div class="container py-12">
        <div class="my-4 lg">
     
          <div class="flex flex-wrap items-center">
            <div class="w-full lg:w-1/3">
              <select-input
                        v-model=ba_provider
                        label="Blockchain Analytics Provider"
                        placeholder="Choose Blockchain Analytics Provider"
                        name="ba_provider"
                        :options=blockchainAnalyticsProviders
                        label-to-show="name"
                        v-validate="'required'"
                        @input="onBAProviderSelected"
                        :error="errors.first('ba_provider')"
                        required
              ></select-input>
            </div>
          </div>
          <div class="flex flex-wrap items-center">
            <div class="w-full lg:w-1/3">
              <select-input
                        v-model=ba_provider_network
                        label="Network"
                        placeholder="Choose Network"
                        name="ba_provider_network"
                        :options=blockchainAnalyticsProvidersNetworks
                        label-to-show="name"
                        v-validate="'required'"
                        
                        :error="errors.first('ba_provider_network')"
                        required
              ></select-input>
            </div>
          </div>
          <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3">
                    <simple-input
                        v-model="walletAddress"
                        label="Crypto Address"
                        placeholder="Choose Crypto Address"
                        name="walletAddress"
                        v-validate="'required'"
                        :error="errors.first('walletAddress')"
                        required
                        disabled
                    ></simple-input>
                </div>
          </div>
          <div class="flex flex-wrap items-center">
                <div class="w-full lg:w-1/3 my-8">
                    <a href="/backoffice/blockchain-analytics-addresses"><button class="btn">
                        Go Back
                    </button></a>
                    
                    <simple-button 
                        :on-click=createBlockchainAnalyticsReport
                        >
                        Create Report
                    </simple-button>

             
                   
                </div>
                
          </div>
          <div class="flex flex-wrap items-center" style="display:block" v-if="ba_provider_report_submitted">
                <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">Report submitted. Waiting for response...</strong></p>
          </div>
          <div class="flex flex-wrap items-center" style="display:block" v-if="ba_provider_report">
                <div class="flex flex-wrap items-center">
                    <p class="md:flex md:items-center"><img src="/images/icon-checkmark.svg" alt="Checkmark" class="mr-2"> <strong class="mr-2">Attention - New Analytics Report <a v-bind:href="ba_provider_report">Available</a></strong></p>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('partials.footer')

@endsection('content')

