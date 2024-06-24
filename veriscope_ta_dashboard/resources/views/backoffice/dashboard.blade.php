@extends('layouts.backoffice')
@section('body-styles')
dashboard
@endsection('body-styles')
@section('content')
<div class="content bg-ash">
  <div class="container py-12">
    <div class="md:flex md:flex-wrap">

      <!-- verified trust anchors -->
      <div class="md:w-1/2 lg:w-1/4 p-4">
        <div class="card">
          <div class="card__header">
            {{ __('Verified Trust Anchors') }}
            <a href="#0" class="tooltipTrigger" v-tooltip="{ content: 'Verified Trust Anchors', trigger: 'click hover focus'}"><img src="/images/icon-info.svg" alt="Info"></a>
          </div>

          <div class="card__body text-center">
            <div>
              <h1 class="mb-2">{{ $verified_trust_anchors }}</h1>
              <p>
                <strong>{{ Str::plural('Verified Trust Anchor', $verified_trust_anchors) }}</strong><br>
              </p>
            </div>
          </div>

          <div class="card__footer">
            <a href="{{ route('verifiedtrustanchors') }}">{{ __('Verified Trust Anchor') }}</a>
          </div>
        </div>
      </div>
      <!-- end verified trust anchors -->
      <!-- discovery layer -->
      <div class="md:w-1/2 lg:w-1/4 p-4">
        <div class="card">
          <div class="card__header">
            {{ __('Discovery Layer') }}
            <a href="#0" class="tooltipTrigger" v-tooltip="{ content: 'View Discovery Layer', trigger: 'click hover focus'}"><img src="/images/icon-info.svg" alt="Info"></a>
          </div>

          <div class="card__body text-center">
            <div>
              <h1 class="mb-2">{{ $trust_anchors }}</h1>
              <p>
                <strong>{{ Str::plural('Discovery Layer', $trust_anchors) }}</strong><br>
              </p>
            </div>
          </div>

          <div class="card__footer">
            <a href="{{ route('discovery') }}">{{ __('View Discovery Layer') }}</a>
          </div>
        </div>
      </div>
      <!-- end approved users -->

      <!-- attestations -->
      <div class="md:w-1/2 lg:w-1/4 p-4">
        <div class="card">
          <div class="card__header">
            {{ __('Attestations') }}
            <a href="#0" class="tooltipTrigger" v-tooltip="{ content: 'All Attestations', trigger: 'click hover focus'}"><img src="/images/icon-info.svg" alt="Info"></a>
          </div>

          <div class="card__body text-center">
            <div>
              <h1 class="mb-2">{{ $attestations }}</h1>
              <p>
                <strong>{{ Str::plural('Attestation', $attestations) }}</strong><br>
              </p>
            </div>
          </div>

          <div class="card__footer">
            <a href="{{ route('blockexplorer') }}">{{ __('View Attestations') }}</a>
          </div>
        </div>
      </div>
      <!-- end trust anchors -->

      <!-- fatf reports -->
      <div class="md:w-1/2 lg:w-1/4 p-4">
        <div class="card">
          <div class="card__header">
            {{ __('KYC Templates') }}
            <a href="#0" class="tooltipTrigger" v-tooltip="{ content: 'View all KYC Templates'}"><img src="/images/icon-info.svg" alt="Info"></a>
          </div>

          <div class="card__body text-center">
            <div>
              <h1 class="mb-2">{{ $kyc_templates }}</h1>
              <p>
                <strong>{{ Str::plural('KYC Template', $kyc_templates) }}</strong><br>
              </p>
            </div>
          </div>

          <div class="card__footer">
            <a href="{{ route('kyctemplates.index') }}">{{ __('View KYC Templates') }}</a>
          </div>
        </div>
      </div>
      <!-- end fatf reports -->

      <!-- Blockchain analytics reports -->
      <div class="md:w-1/2 lg:w-1/4 p-4">
        <div class="card">
          <div class="card__header">
            {{ __('Blockchain Analytics') }}
            <a href="#0" class="tooltipTrigger" v-tooltip="{ content: 'View all Blockchain Analytics Reports'}"><img src="/images/icon-info.svg" alt="Info"></a>
          </div>

          <div class="card__body text-center">
            <div>
              <h1 class="mb-2">{{ $ba_addresses }}</h1>
              <p>
                <strong>{{ Str::plural('Blockchain Analytics Reports', $ba_addresses) }}</strong><br>
              </p>
            </div>
          </div>

          <div class="card__footer">
            <a href="{{ route('blockchainanalyticsaddresses') }}">{{ __('View Blockchain Analytics') }}</a>
          </div>
        </div>
      </div>
      <!-- end Blockchain analytics reports -->
    </div>
  </div>
</div>
@endsection('content')
