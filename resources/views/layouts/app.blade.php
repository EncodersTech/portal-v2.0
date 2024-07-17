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
    
    <!-- <style>
        #support_question {
            position: absolute;
            z-index: 9;
            /* background-color: #f1f1f1; */
            border: 1px solid #d3d3d3;
            text-align: center;
            top: 0;
            right: 0;
            width: 5em;
        }

        #mydivheader {
            padding: 2px;
            cursor: move;
            z-index: 10;
            background-color: #2196F3;
            color: #fff;
        }
    </style>
    <div id="support_question">
        <div id="mydivheader">Question</div>
        <a href="#modal-contact-us" data-toggle="modal" data-backdrop="static" data-keyboard="false">
            <i class="fa fa-question-circle" aria-hidden="true" style="font-size:3em;"></i>
        </a>
        
    </div>
    <script>
        dragElement(document.getElementById("support_question"));

        function dragElement(elmnt) {
            var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
            if (document.getElementById(elmnt.id + "header")) {
                // if present, the header is where you move the DIV from:
                document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
            } else {
                // otherwise, move the DIV from anywhere inside the DIV:
                elmnt.onmousedown = dragMouseDown;
            }

            function dragMouseDown(e) {
                e = e || window.event;
                e.preventDefault();
                // get the mouse cursor position at startup:
                pos3 = e.clientX;
                pos4 = e.clientY;
                document.onmouseup = closeDragElement;
                // call a function whenever the cursor moves:
                document.onmousemove = elementDrag;
            }

            function elementDrag(e) {
                e = e || window.event;
                e.preventDefault();
                // calculate the new cursor position:
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                // set the element's new position:
                elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
                elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
            }

            function closeDragElement() {
                // stop moving when mouse button is released:
                document.onmouseup = null;
                document.onmousemove = null;
            }
        }
    </script> -->
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
        Tawk_API.onStatusChange = function (status){
            if(status === 'online'){
                document.getElementById('live_chat_div').classList.remove('d-none');
            }else{
                document.getElementById('live_chat_div').classList.add('d-none');
            }
        };
        (function(){
        var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
        s1.async=true;
        s1.src='https://embed.tawk.to/5b4fd91adf040c3e9e0bb714/default';
        s1.charset='UTF-8';
        s1.setAttribute('crossorigin','*');
        s0.parentNode.insertBefore(s1,s0);
        })();
    </script>
    <!--End of Tawk.to Script-->
    <script>
        window.laravel_echo_port='{{env("LARAVEL_ECHO_PORT")}}';
        window.server_env = '{{env("APP_ENV")}}';
    </script>
    <?php 
        if(env('APP_ENV') == 'local'){
            $socket_url = 'http://'.Request::getHost().':'.env('LARAVEL_ECHO_PORT');
        }else{
            $socket_url = 'https://'.Request::getHost();
        }
    ?>
    <script src="{{ $socket_url }}/socket.io/socket.io.js"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    <!-- Scripts -->
    @section('scripts')

    @show

</body>

</html>