@extends('layouts.app')

@section('styles')
    <link href="{{ mix('css/auth.css') }}" rel="stylesheet">
@endsection

@section('content')

    @include('include.modals.contact_us')

    <div id="app" class="global-container">
        <div class="px-3 py-3">
            <a href="{{ config('app.main_website', '#') }}" class="text-danger">
                <span class="fas fa-long-arrow-alt-left"></span> <span class="fas fa-home"></span> Back to homepage
            </a>
        </div>
        <div class="auth-container">
            <div class="auth-box">
                <div class="container-fluid">
                    <div class="auth-form p-3 rounded mb-3">
                        <img src="{{ asset('img/logos/red_and_white_transparent.png') }}" class="auth-form-logo my-3 mx-auto">

                        @include('include.alerts')

                        @yield('content-auth')
                    </div>
                    <div class="mb-3 text-center">
                            <span class="fas fa-question-circle"></span> Need help ?
                            <a href="#modal-contact-us" class="text-danger .contact-us-cta"
                                data-toggle="modal" data-backdrop="static" data-keyboard="false">
                                Contact us.
                            </a>
                    </div>
                    <div class="text-center">
                            <span class="far fa-copyright"></span> {{ date('Y') . ' ' . config('app.copyright_text') }}
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endsection

@section('scripts')
    @section('scripts-auth')
        <script src="{{ mix('js/auth/auth.js') }}"></script>
    @show()

    <script src="{{ mix('js/include/modals/contact_us.js') }}"></script>
@endsection
