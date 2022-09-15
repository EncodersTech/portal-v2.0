@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-sign-in-alt"></span> Register for {{ $meet->name }}
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="mb-3">
                    <a href="{{ $meet->profile_picture }}" target="_blank">
                        <img id="profile-picture-display" src="{{ $meet->profile_picture }}"
                            class="rounded profile-picture-256" alt="Meet Picture">
                    </a>
                </div>

                @include('include.meet.sidebar_info')
            </div>

            <div class="col">
                @if ($meet->registrationStatus() == App\Models\Meet::REGISTRATION_STATUS_LATE)
                    <div class="alert alert-warning">
                        <span class="fas fa-exclamation-circle"></span> This meet is open for LATE registations.
                    </div>
                @endif
                <div class="text-info small mb-3">
                    <span class="fas fa-info-circle"></span> All dates and times are in EST.
                </div>

                <div class="alert alert-danger" :class="{'d-block': isError}" style="display: none">
                    <span class="fas fa-fw fa-times-circle"></span> Whoops !<br/>
                    <div class="mt-1" v-html="errorMessage"></div>
                </div>

                <div :class="{'d-none': isError}">
                    <div :class="{'d-none': step != 1}">
                        @include('registration.register.levels')
                    </div>

                    <div :class="{'d-none': step != 2}">
                        <div class="small" :class="{'d-none': !paymentOptionsLoading}">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                            </span> Loading payment options, please wait ...
                        </div>
                        <div :class="{'d-none': paymentOptionsLoading}">
                            @include('registration.register.payment')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script>
        window.meetId = {{ $meet->id }};
        window._managed_account = {{ $_managed->id }};
    </script>
    <script src="{{ mix('js/register/register.js') }}"></script>
@endsection
