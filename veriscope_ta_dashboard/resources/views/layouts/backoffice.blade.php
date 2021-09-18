<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Shyft Network Inc.</title>

    <meta property="og:title" content="Shyft Network Inc.">
    <meta property="og:description" content="The credibility network for the entire global economy.">
    <meta property="og:image" content="https://www.shyft.network/images/shyft-logo-horizontal-tm@3x.png">
    <meta property="og:url" content="https://www.shyft.network/">

    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/favicon/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/favicon/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/favicon/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/favicon/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="/favicon/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="/favicon/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="/favicon/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="/favicon/apple-touch-icon-152x152.png" />
    <link rel="icon" type="image/png" href="/favicon/favicon-196x196.png" sizes="196x196" />
    <link rel="icon" type="image/png" href="/favicon/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="/favicon/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/favicon/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="/favicon/favicon-128.png" sizes="128x128" />
    <meta name="application-name" content="Shyft Network Inc."/>
    <meta name="msapplication-TileColor" content="#FFFFFF" />
    <meta name="msapplication-TileImage" content="/favicon/mstile-144x144.png" />
    <meta name="msapplication-square70x70logo" content="/favicon/mstile-70x70.png" />
    <meta name="msapplication-square150x150logo" content="/favicon/mstile-150x150.png" />
    <meta name="msapplication-wide310x150logo" content="/favicon/mstile-310x150.png" />
    <meta name="msapplication-square310x310logo" content="/favicon/mstile-310x310.png" />

    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#A72155">

    <!-- these are just for the demo page / options -->
    <script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>


    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ Auth::user()->id }}">
    @endauth

    <!-- Scripts -->
    @yield('scripts')
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Styles -->
    @yield('styles')
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-N97XWCX');</script>
    <!-- End Google Tag Manager -->

    <!-- JS Accessible Flag -->
    <script>
        document.documentElement.className = (document.documentElement.className !== '') ? ' js' : 'js';
    </script>
</head>
<body class="preload @yield('body-styles')">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N97XWCX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    @include('partials.navigation')

    <div class="content" id="backoffice">
      @yield('content')
    </div>

    <!-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script> -->

    @include('partials.scripts')

</body>
</html>
