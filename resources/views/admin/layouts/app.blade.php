<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 4.1.1 -->
    <link href="{{ asset('assets/admin/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>

    <!-- Ionicons -->
    <link href="{{ asset('fonts/vendor/@fortawesome/fontawesome-free/css/all.css') }}" rel="stylesheet" type="text/css">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/adminlte.css') }}">

    <!-- Other CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/css/iziToast.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-datetimepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/sweetalert.css') }}" type="text/css"/>

    @yield('page_css')

    <link href="{{mix('assets/admin/style/style.css')}}" rel="stylesheet" type="text/css"/>

    @yield('css')

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        @include('admin.layouts.header')
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        @include('admin.layouts.sidebar')
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        @yield('content')
    </div>

    {{--    <footer class="main-footer">--}}
    {{--    </footer>--}}
</div>
</body>

<script src="{{ asset('assets/admin/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/jquery.nicescroll.js') }}"></script>
<script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/sweetalert.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/iziToast.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap-datetimepicker.min.js') }}"></script>

<!-- Template JS File -->
<script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>


@yield('page_js')

<script src="{{mix('assets/admin/js/custom.js')}}" type="text/javascript"></script>

@yield('scripts')

<script>
    $(document).ready(function () {
        $('.alert').delay(4000).slideUp(300);
    });

    // Loading button plugin (removed from BS4)
    (function ($) {
        $.fn.button = function (action) {
            if (action === 'loading' && this.data('loading-text')) {
                this.data('original-text', this.html()).html(this.data('loading-text')).prop('disabled', true);
            }
            if (action === 'reset' && this.data('original-text')) {
                this.html(this.data('original-text')).prop('disabled', false);
            }
        };
    }(jQuery));

</script>
