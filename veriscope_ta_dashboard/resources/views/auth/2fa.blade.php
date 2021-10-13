@extends('layouts.app')
@section('body-styles')
settings
@endsection('body-styles')
@section('content')
<div class="md:flex min-h-screen-nav">
    <div class="px-6 py-16 bg-ash flex justify-center md:w-1/2">
        <div class="self-center max-w-xs">
            <h1 class="h2">{{ __('Two Factor Authentication') }}</h1>
            <p>Two factor authentication (2FA) strengthens access security by requiring two methods (also referred to as factors) to verify your identity. Two factor authentication protects against phishing, social engineering and password brute force attacks and secures your logins from attackers exploiting weak or stolen credentials.</p>
            <!-- <br/>
      <p>To Enable Two Factor Authentication on your Account, you need to do following steps</p>
      <strong>
      <ol>
          <li>Click on Generate Secret Button , To Generate a Unique secret QR code for your profile</li>
          <li>Verify the OTP from Google Authenticator Mobile App</li>
      </ol>
      </strong> -->
        </div>
    </div>
    <div class="px-6 py-16 bg-white flex justify-center md:w-1/2">
        <div class="self-center w-full max-w-xs">
            <strong>Two Factor Authentication</strong>
            <div class="panel-body">
                <br/>
                @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
                @endif @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                @if(empty($data['user']->passwordSecurity))
                <form class="form-horizontal" method="POST" action="{{ route('generate2faSecret') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <button type="submit" class="btn btn-primary">
                               Generate Secret Key to Enable 2FA
                            </button>
                        </div>
                    </div>
                </form>
                @elseif(!$data['user']->passwordSecurity->google2fa_enable)
                <p><strong>1.</strong> Scan this barcode with your Google Authenticator App or manually enter the secret key into your authenticator app:</p>
                <img src="{{$data['google2fa_url'] }}" alt="">
                {{ Form::simpleInput('text', 'secret-key', 'Secret Key', Auth::user()->passwordSecurity->google2fa_secret) }}
                <br/><br/>
                <p><strong>2.</strong> Enter the pin the code to Enable 2FA</p>
                <form class="form-horizontal" method="POST" action="{{ route('enable2fa') }}">
                    {{ csrf_field() }}

                    <div class="form-group{{ $errors->has('verify-code') ? ' has-error' : '' }}">
                        <div class="col-md-6">
                            {{ Form::simpleInput('password', 'verify-code', 'Enter your Authenticator Code', old('verify-code')) }}

                            @if ($errors->has('verify-code'))
                              <span class="help-block">
                              <strong>{{ $errors->first('verify-code') }}</strong>
                              </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <button type="submit" class="btn btn-primary">
                               Enable 2FA
                           </button>
                        </div>
                    </div>
                </form>
                @elseif($data['user']->passwordSecurity->google2fa_enable)
                <div class="alert alert-success">
                    2FA is Currently <strong>Enabled</strong> for your account.
                </div>
                <p>If you are looking to disable Two Factor Authentication. Please confirm your password and Click Disable 2FA Button.</p>
                <form class="form-horizontal" method="POST" action="{{ route('disable2fa') }}">
                    <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                        <div class="col-md-6">
                            {{ Form::simpleInput('password', 'current-password', 'Current Password', old('current-password')) }}
                            @if ($errors->has('current-password'))
                              <span class="help-block">
                                <strong>{{ $errors->first('current-password') }}</strong>
                              </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 col-md-offset-5">

                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-primary ">Disable 2FA</button>
                    </div>
                </form>
                @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
