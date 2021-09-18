<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>Veriscope</title>

    <meta property="og:title" content="Veriscope">
    <meta property="og:description" content="The complete solution to the FATF Travel Rule and global regulatory compliance.">
    <meta property="og:image" content="https://veriscope.network/images/versiscope@3x.png">
    <meta property="og:url" content="https://veriscope.network/">

    <!-- <link rel="apple-touch-icon-precomposed" sizes="57x57" href="/favicon/apple-touch-icon-57x57.png" />
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
    <meta name="msapplication-square310x310logo" content="/favicon/mstile-310x310.png" /> -->
    <link rel="apple-touch-icon" sizes="120x120" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <!-- <link rel="manifest" href="/site.webmanifest"> -->
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/manifest.json">
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
    <meta name="theme-color" content="#A72155">

    <link href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700" rel="stylesheet">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ Auth::user()->id }}">
    @endauth
    @if(config('app.debug') == true)
    <meta name="debug" content="true">
    @endif
    <!-- Scripts -->
    @yield('scripts')
    <!-- <script src="{{ asset('js/app.js') }}" defer></script> -->
    <script src="/js/app.js" defer></script>

    <!-- Styles -->
    @yield('styles')
    <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->
    <link href="/css/app.css" rel="stylesheet">
    <link href="/css/globe.css" rel="stylesheet">

  <!-- Google Tag Manager -->
  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-172688188-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-172688188-1');
</script>

  <!-- End Google Tag Manager -->

  <!-- JS Accessible Flag -->
  <script>
    document.documentElement.className = (document.documentElement.className !== '') ? ' js' : 'js';
  </script>
  <script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
  <script src="/js/Detector.js"></script>
  <script src="/js/grid.js"></script>
  <script src="/js/globe.js"></script>

        <script>

            var globe,
                globeCount = 0;


            function createGlobe(){
                var newData = [];
                globeCount++;
                $("#globe canvas").remove();

                globe = new ENCOM.Globe(window.innerWidth, window.innerHeight, { tiles: grid.tiles });

                $("#globe").append(globe.domElement);
                globe.init(start);
            }

            function onWindowResize(){
                globe.camera.aspect = window.innerWidth / window.innerHeight;
                globe.camera.updateProjectionMatrix();
                globe.renderer.setSize(window.innerWidth, window.innerHeight);

            }

            function roundNumber(num){
                return Math.round(num * 100)/100;
            }

            function projectionToLatLng(width, height, x,y){

                return {
                    lat: 90 - 180*(y/height),
                    lon: 360*(x/width)- 180,
                };

            }

            function animate(){

                if(globe){
                    globe.tick();
                }

                lastTickTime = Date.now();

                requestAnimationFrame(animate);
            }

            function start(){
                if(globeCount == 1){ // only do this for the first globe that's created. very messy
                    animate();
                }
            }

            $(function() {
                var open = false;

                if(!Detector.webgl)
                {
                    Detector.addGetWebGLMessage({parent: document.getElementById("container")});
                    return;
                }

                window.addEventListener( 'resize', onWindowResize, false );

                var docHeight = $(document).height();

                /* Webgl stuff */
                createGlobe();

            });

        </script>
</head>
<body class="preload @yield('body-styles')">

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N97XWCX"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    @if(Request::getPathInfo() == '/')
    @include('partials.navigation-welcome')
    @else
    @include('partials.navigation')
    @endif

    @yield('content')

    @include('partials.scripts')

    @yield('endscripts')
</body>
</html>
