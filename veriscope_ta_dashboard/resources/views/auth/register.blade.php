@extends('layouts.app')

@section('body-styles') layout--halves @endsection('body-styles')

@section('content')
<h1>THIS PAGE IS NO LONGER NEEDED</h1>
<div id="kyc" class="bg-ash">
    <transition name="fade">
        <router-view></router-view>
    </transition>
</div>
@endsection
