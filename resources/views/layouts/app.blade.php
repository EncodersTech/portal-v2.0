<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Styles -->
    @section('styles')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @show

</head>

<body>
    <div id="app">
        @yield('navbar')

        @yield('content')
    </div>

    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    // var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    // (function(){
    //     var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    //     s1.async=true;
    //     s1.src='https://embed.tawk.to/5b4fd91adf040c3e9e0bb714/default';
    //     s1.charset='UTF-8';
    //     s1.setAttribute('crossorigin','*');
    //     s0.parentNode.insertBefore(s1,s0);
    // })();
    </script>
    <!--End of Tawk.to Script-->
        <script>
            // window.laravel_echo_port='{{env("LARAVEL_ECHO_PORT")}}';
        </script>
        <!-- <script src="//{{ Request::getHost() }}:{{env('LARAVEL_ECHO_PORT')}}/socket.io/socket.io.js"></script> -->
        <!-- <script src="{{ mix('js/app.js') }}"></script> -->
    <!-- Scripts -->
    @section('scripts')

    @show

</body>

</html>