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
                @if($meet->id == 168)
                    <div class="alert alert-info">
                        <div class="d-flex flex-row flex-nowrap">
                            <div class="flex-grow-1">
                                <span class="fas fa-info-circle"></span>
                                Having trouble registering event specialists in a level different from their AA level? 
                                <a target="__blank" class="btn btn-success" href="https://allgymnastics.zendesk.com/hc/en-us/articles/17671901030937-Registering-USAIGC-Event-Specialists">
                                    Click here for instructions
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
                @if($meet->id == 322)
                    <div class="alert alert-info">
                        <div class="d-flex flex-row flex-nowrap">
                            <div class="flex-grow-1">
                                <span class="fas fa-info-circle"></span>
                                If you are registering as an Even Specialist, the fee is the same. Register as AA and email support@allgymnastics.com with Gym Name, Athlete Name, Level and Events
                            </div>
                        </div>
                    </div>
                @endif

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
                        <div class="alert alert-info small mb-3">
                            <span class="fas fa-info-circle"></span> ACH (Saved Account Info) and One Time ACH (Easy Pay)
                        </div>
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
    
    <script>
        function toogle_size_chart() {
            var x = document.getElementById("size_chart_list");
            if (x.style.display === "none") {
                x.style.display = "block";
                x.previousElementSibling.children[0].classList.add('fa-caret-down');
                x.previousElementSibling.children[0].classList.remove('fa-caret-right');
            } else {
                x.style.display = "none";
                x.previousElementSibling.children[0].classList.remove('fa-caret-down');
                x.previousElementSibling.children[0].classList.add('fa-caret-right');
            }
        }
    </script>
@endsection
