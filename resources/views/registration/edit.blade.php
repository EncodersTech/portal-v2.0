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
                <div>
            <h6 for="" class="alert alert-success" style="cursor:pointer;" onclick="toogle_size_chart()">
                View Sizing Charts
                <span class="fas fa-caret fa-caret-down "></span>
            </h6>
            <ul id="size_chart_list">
                <li>
                    <a href="https://gkelite.azureedge.net/images/static/sizecharts/size-charts-inches-womens-leos.pdf" target="_blank">
                    GK in inches</a>
                </li>
                <li>
                    <a href="https://gkelite.azureedge.net/images/static/sizecharts/size-charts-centimeters-womens-leos.pdf" target="_blank">
                    GK in metric</a>
                </li>
                <li>
                    <a href="https://www.snowflakedesigns.com/sizing-information" target="_blank">
                    SnowFlake</a>
                </li>
                <li>
                    <a href="https://destira.com/pages/size-chart" target="_blank">
                    Destira</a>
                </li>
                <li>
                    <a href="https://www.higoapparel.com/sizing-chart" target="_blank">
                    Higo</a>
                </li>
            </ul>
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
    <script src="{{ mix('js/register/edit.js') }}"></script>
@endsection