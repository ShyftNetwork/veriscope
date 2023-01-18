<div class="main-navigation" id="main-navigation">
  <div class="main-navigation__top">
    @auth
      @if(Config::get('shyft.onboarding'))
      <a href="{{ route('manage-organization')}}" class="main-navigation__logo">
      @elseif(Config::get('backoffice.enabled'))
      <a href="{{ route('backoffice.dashboard')}}" class="main-navigation__logo">
      @else
      <a href="/auth/welcome" class="main-navigation__logo">
      @endif
    @else
    <a href="/" class="main-navigation__logo">
    @endauth
    <div class="shyft-logo"><img src="/images/veriscope-logo.svg" width="93" height="45" alt="Shyft Logo" /></div><div class="shyft-logo-white"><img src="/images/veriscope-logo-white.svg" width="93" height="45" alt="Shyft Logo" /></div></a>
    @if (Route::has('login'))
        @auth
          @if(Auth::user()->inGroup('admin') || Auth::user()->inGroup('member'))
            <div class="auth-welcome">
              <div class="flex-grow pb-2 pr-6">Welcome back, {{ Auth::user()->first_name }}</div>
              <div>
                @php
                  $avatar = strtolower(Auth::user()->gender);
                  if($avatar != 'male' && $avatar != 'female') {
                    $avatar = 'none';
                  }
                @endphp
                <img src="/images/avatars/{{ $avatar }}.svg" width="39" height="39" />
              </div>
            </div>
          @endif
          <div class="hamburger" id="main-navigation__hamburger">
            <div class="hamburger__line line-1"></div>
            <div class="hamburger__line line-2"></div>
            <div class="hamburger__line line-3"></div>
          </div>
        @elseif(Config::get('shyft.onboarding'))
          <div class="top-right links">
            <div class="links__login">
              <span class="hidden md:inline">View Documentation Suite&nbsp;&nbsp;</span>
              <a href="https://docs.veriscope.network">Here</a>
              <span class="hidden md:inline">Are you already a user?&nbsp;&nbsp;</span>
              <a href="{{ route('login') }}">Sign In</a>
            </div>
          </div>
        @endauth
    @endif
  </div>
  @auth
    <ul class="main-subnavigation pt-6 md:pt-0">
      @if(str_contains(Request::getPathInfo(), 'backoffice') && Config::get('backoffice.enabled'))
        @if(Config::get('shyft.onboarding'))
          <li><img src="/images/nav-icons/sign-out.svg" width="20" height="20"> <a href="{{ route('manage-organization') }}">Exit Backoffice</a></li>
        @endif
        @if(Auth::user()->inGroup('admin'))
          <li id="sub-dashboard"><img src="/images/nav-icons/dashboard.svg" width="20" height="20"> <a href="{{ route('backoffice.dashboard') }}">Dashboard</a></li>
          <li id="sub-users"><a href="{{ route('horizon.index') }}" target="_blank">Horizon</a></li>
          <li id="sub-users"><a href="{{ route('arena.auth') }}" target="_blank">Arena</a></li>
          <li id="sub-users"><img src="/images/nav-icons/users.svg" width="20" height="20"> <a href="{{ route('kyctemplates.index') }}">Kyc Templates</a></li>
        @endif
      @else
        @if(Config::get('backoffice.enabled') && Auth::user()->inGroup('admin'))
          <li><img src="/images/nav-icons/shyft-id.svg" width="20" height="20"> <a href="{{ route('backoffice.dashboard') }}">Backoffice</a></li>
        @endif
        @if(Config::get('shyft.onboarding'))
          <li id="sub-trust-anchor-setup"><img src="/images/nav-icons/settings.svg" width="20" height="20"> <a href="{{ route('manage-organization') }}">Trust Anchor Admin</a></li>
          <li id="sub-settings"><img src="/images/nav-icons/settings.svg" width="20" height="20"> <a href="{{ route('settings') }}">Settings</a></li>
        @endif
      @endif
      <li><img src="/images/nav-icons/sign-out.svg" width="20" height="20" alt="Sign Out"> <a href="{{ route('logout') }}">Sign Out</a></li>
    </ul>
  @endauth
</div>
@if(count($errors))
  <div class="notification notification--error" id="notification">
    <p><img src="/images/notifications/error.svg">
      @foreach($errors->all() as $error)
      {{ $error }}<br>
      @endforeach
    </p>
  </div>
@elseif ( Session::has('flash_message') )
  <div class="notification notification--{{ Session::get('flash_type') }}" id="notification">
      <p>
        @if(Session::get('flash_type')=='success')
        <img src="/images/notifications/success.svg">
        @elseif(Session::get('flash_type')=='success')
        <img src="/images/notifications/error.svg">
        @else
        <img src="/images/notifications/general.svg">
        @endif
        {{ Session::get('flash_message') }}
      </p>
      <a class="notification__close">
        <img src="/images/icon-close.svg" width="16" height="16" />
      </a>
  </div>
@endif
