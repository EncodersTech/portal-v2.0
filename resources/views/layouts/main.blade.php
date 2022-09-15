@extends('layouts.app')
@yield('page_css')
@section('styles')
    <link href="{{ mix('css/main.css') }}" rel="stylesheet">
    <link href="{{mix('assets/user/style/style.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/iziToast.min.css') }}">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"> -->
    <style>
    .notif{
        padding: 3px 5%;
        border-radius: 15px;
        margin-left: 10px;
        display: inline-block;
    }
</style>
@endsection

@section('content')

    @include('include.modals.contact_us')

    <div class="main-container">
        @include('include.nav.sidebar')

        <div class="main-body">
            @include('include.nav.topbar')

            <div class="actual-content">
                <div class="container-fluid">
                    <h3 class="mt-3 font-weight-bold">
                    @yield('content-header')
                    </h3>

                    @include('include.alerts')
                    @yield('content-main')
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-WWSHQETP8Z"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-WWSHQETP8Z');

        (function(h,o,t,j,a,r){
            h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
            h._hjSettings={hjid:2922173,hjsv:6};
            a=o.getElementsByTagName('head')[0];
            r=o.createElement('script');r.async=1;
            r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
            a.appendChild(r);
        })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
    </script>
    @section('scripts-main')
        <script src="{{ mix('js/main.js') }}"></script>
    @show()
    <script src="{{ asset('assets/admin/js/iziToast.min.js') }}"></script>
    <script src="{{ mix('assets/admin/js/custom.js') }}"></script>
    <script src="{{ mix('js/include/modals/contact_us.js') }}"></script>
    <script src="{{ mix('js/include/nav/sidebar.js') }}"></script>
    <script src="{{ mix('js/include/nav/topbar.js') }}"></script>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script> -->
@endsection
