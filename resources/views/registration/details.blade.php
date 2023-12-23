
@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-clipboard-check"></span>
    {{ $gym->name }}'s Registration for {{ $meet->name }}
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <div class="content-main p-3">
        <div class="row">
            <div class="col">
                <div v-if="errorMessage" :class="{'d-block': errorMessage}"  style="display: none">
                    <div class="alert alert-danger">
                        <span class="fas fa-times-circle"></span> <span v-html="errorMessage"></span>
                    </div>
                </div>
                <div v-else-if="isLoading">
                    <div class="small text-center py-3">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true">
                        </span> Loading, please wait ...
                    </div>
                </div>
                <div v-else>
                    @if ($registration->hasRepayableTransactions())
                        <div class="alert alert-danger">
                            <strong>
                                <span class="fas fa-exclamation-circle"></span>
                                This registration has failed transactions.
                            </strong><br/>
                            Please see the "Transactions" tab for more details.
                        </div>
                    @endif

                    @if ($registration->hasPendingTransactions())
                        <div class="alert alert-warning">
                            <strong>
                                <span class="fas fa-info-circle"></span>
                                This registration has a pending transaction.
                            </strong><br/>
                            Please see the "Transactions" tab for more details.
                        </div>
                    @endif

                    @if ($registration->canBeEdited() && $disable_edit == 0)
                        <div class="alert alert-primary d-flex flex-no-wrap flex-row">
                            <div class="flex-grow-1">
                                <span class="fas fa-info-circle"></span> You can make changes to your registration
                                until {{ $registration->meet->registration_scratch_end_date->format(Helper::AMERICAN_SHORT_DATE) }}.
                            </div>
                            <div class="align-self-end">
                                <a class="btn btn-sm btn-success"
                                    href="{{ route('gyms.registration.edit', ['gym' => $gym, 'registration' => $registration]) }}">
                                    <span class="fas fa-edit"></span> Edit 
                                </a>
                            </div>
                        </div>
                    @endif
                    <div v-if="transactions.length > 0">
                        <div v-for="tx in transactions" :key="tx.processor_id">
                            <div v-if="tx.status == constants.transactions.statuses.WaitlistConfirmed" class="alert alert-success d-flex flex-no-wrap flex-row">
                                <span>You have an approved waitlist registration that had been created on @{{ tx.created_at_display }}
                                    and is awaiting payment. Please pay the registration fee from <b>"Transaction"</b> tab as soon as possible to secure your spot in the meet.</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="text-info small mb-3">
                        <span class="fas fa-info-circle"></span> All dates and times are in EST.
                    </div>

                    <div>
                        <ul class="nav nav-tabs" role="tablist" id="registration-list-tabs">
                            <li class="nav-item">
                                <a class="nav-link" :class="{'active': tab == 'details'}"
                                    href='#' role="tab" @click="tab = 'details'">
                                    Details
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{'active': tab == 'transactions'}"
                                    href='#' role="tab" @click="tab = 'transactions'">
                                    Transactions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{'active': tab == 'reports'}"
                                    href='#' role="tab" @click="tab = 'reports'">
                                    Reports
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content py-3">
                        <div class="tab-pane fade" :class="{'active show' : tab == 'details'}"
                            role="tabpanel">
                            @include('registration.details.athletes')
                            @include('registration.details.coaches')
                        </div>

                        <div class="tab-pane fade" :class="{'active show': tab == 'transactions'}"
                            role="tabpanel">
                            @include('registration.details.transactions')
                        </div>

                        <div class="tab-pane fade" :class="{'active show': tab == 'reports'}"
                            role="tabpanel">
                            @include('registration.details.reports')
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-3">
                <div class="mb-3">
                    <a href="{{ $meet->profile_picture }}" target="_blank">
                        <img id="profile-picture-display" src="{{ $meet->profile_picture }}"
                            class="rounded profile-picture-256" alt="Meet Picture">
                    </a>
                </div>

                @include('include.meet.sidebar_info')
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script>
        window._managed_account = {{ $_managed->id }};
        window._gym = {{ $gym->id }};
        window._registration = {{ $registration->id }};
    </script>
    <script src="{{ mix('js/register/details.js') }}"></script>
@endsection
