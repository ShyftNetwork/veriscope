@extends('layouts.app')

@section('content')
<div id="attestations" class="bg-ash">
    <transition name="fade">
        <router-view></router-view>
    </transition>
</div>
@endsection
