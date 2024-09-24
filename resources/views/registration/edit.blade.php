@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-edit"></span> Edit {{ $gym->name }}'s registration in {{ $meet->name }}
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
                <div class="alert alert-info" id="clickdiv">
                    <span class="fas fa-info-circle"></span>
                    <strong>Note:</strong> Double click fields to edit athletes or coaches Information
                </div>

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
                        @include('registration.edit.levels')
                    </div>

                    <div :class="{'d-none': step != 2}">
                        <div class="small" :class="{'d-none': !paymentOptionsLoading}">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                            </span> Loading payment options, please wait ...
                        </div>
                        <div :class="{'d-none': paymentOptionsLoading}">
                            <div class="alert alert-info small mb-3">
                                <span class="fas fa-info-circle"></span> ACH (Saved Account Info) and One Time ACH (Easy Pay)
                            </div>
                            @include('registration.edit.payment')
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
        window.gymId = {{ $gym->id }};
        window.registrationId = {{ $registration->id }};
        window._managed_account = {{ $_managed->id }};
    </script>
    <script src="{{ mix('js/register/edit.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

@endsection