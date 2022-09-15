@extends('layouts.main')
@section('page_css')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}"  type="text/css"/>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
        
    <style>
        .tableFixHead thead th { position: sticky; top: -1px; background:#eee;}
        .image__file-upload{
            padding: 10px;
            background: #0a5b7a;
            display: table;
            color: #fff;
            border-radius: .25rem;
            border-color: #0a5b7a;
            cursor: pointer;
        }
        .thumbnail-preview {
            max-width: 100px !important;
            height: 70px !important;
       }

        .selectGymDiv{
            height: 350px;
            overflow: auto;
        }
        .info-box {
            display: block;
            min-height: 90px;
            background: #fff;
            width: 100%;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            border-radius: 2px;
            margin-bottom: 15px;
        }
        .info-box-icon {
            border-top-left-radius: 2px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 2px;
            display: block;
            float: left;
            height: 90px;
            width: 90px;
            text-align: center;
            font-size: 45px;
            line-height: 90px;
            background: rgba(0,0,0,0.2);
        }
        .info-box-content {
            padding: 5px 10px;
            margin-left: 90px;
        }
        .fa-fa-setting{
            color: white;
            margin-top: 20px;
        }
        .info-box-text {
            text-transform: uppercase;
        }
        .progress-description, .info-box-text {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .info-box-number {
            display: block;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
@endsection
@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span> {{ $meet->name }}'s Dashboard
@endsection

@section('content-main')
    @include('include.errors')

    <div class="alert alert-primary">
        <div class="d-flex flex-row flex-nowrap">
            <div class="flex-grow-1">
                <span class="fas fa-info-circle"></span>
                You are the host of this meet. This dashboard will provide you with registration
                and financial data for this meet.
            </div>
            <div>
                <a href="{{ url()->previous() }}"
                    class="btn btn-sm btn-secondary">
                    <span class="fas fa-long-arrow-alt-left"></span> Back
                </a>
            </div>
        </div>
    </div>

    <div class="content-main p-3">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="mb-3">
                    <a href="{{ $meet->profile_picture }}" target="_blank">
                        <img id="profile-picture-display" src="{{ $meet->profile_picture }}"
                            class="rounded profile-picture-256" alt="Meet Picture">
                    </a>
                </div>

                <ul class="nav flex-column nav-pills" role="tablist" id="meet-dashboard-list-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="#" role="tab"
                            :class="{'active': tab == 'summary'}" @click="switchToTab('summary')">
                            <span class="fas fa-fw fa-align-justify"></span> Summary
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#" role="tab"
                            :class="{'active': tab == 'participants'}" @click="switchToTab('participants')">
                            <span class="fas fa-fw fa-users"></span> Participants
                            <span v-if="registrations.length > 0" class="badge badge-success custom-badge">
                                @{{ registrations.length }}
                            </span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#transactions-tab" role="tab"
                            :class="{'active': tab == 'transactions'}" @click="switchToTab('transactions')">
                            <span class="fas fa-fw fa-exchange-alt"></span> Transactions
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#deposit-tab" role="tab"
                            :class="{'active': tab == 'deposit'}" @click="switchToTab('deposit')">
                            <span class="fas fa-fw fa-money-check"></span> Deposit
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#" role="tab"
                            :class="{'active': tab == 'mailer'}" @click="switchToTab('mailer')">
                            <span class="fas fa-fw fa-mail-bulk"></span> Email Participants
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#" role="tab"
                            :class="{'active': tab == 'export'}" @click="switchToTab('export')">
                            <span class="fas fa-fw fa-file-export"></span> Export &amp; Reports
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#" role="tab"
                            :class="{'active': tab == 'usag'}" @click="switchToTab('usag')">
                            <span class="fas fa-fw fa-cloud-download-alt"></span> USAG Reservations
                        </a>
                    </li>
                </ul>

                @include('include.meet.sidebar_info')
            </div>

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
                    <div class="text-info small mb-1">
                        <span class="fas fa-info-circle"></span> All dates and times are in EST.
                    </div>

                    <div v-if="cardError" class="text-danger small mb-3">
                        <span class="fas fa-exclamation-triangle"></span> <span v-html="cardError"></span>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade" :class="{'show active': tab == 'summary'}"
                            id="summary-tab" role="tabpanel">
                            @include('host.meet.details.summary')
                        </div>
                        <div class="tab-pane fade" :class="{'show active': tab == 'participants'}"
                            id="participants-tab" role="tabpanel">
                            @include('host.meet.details.participants')
                        </div>
                        <div class="tab-pane fade" :class="{'show active': tab == 'transactions'}"
                            id="transactions-tab" role="tabpanel">
                            @include('host.meet.details.transactions')
                        </div>
                        <div class="tab-pane fade" :class="{'show active': tab == 'deposit'}"
                            id="deposit-tab" role="tabpanel">
                            @include('host.meet.details.deposit')
                        </div>

                        <div class="tab-pane fade" :class="{'show active': tab == 'mailer'}"
                            id="mailer-tab" role="tabpanel">
                            <form method="POST" action="{{route('send-mass-notification')}}" id="submitMassNotification" enctype="multipart/form-data">
                            @csrf
                                @include('host.meet.details.mailer')
                            </form>
                        </div>
                        <div class="tab-pane fade" :class="{'show active': tab == 'export'}"
                            id="export-tab" role="tabpanel">
                            @include('host.meet.details.export')
                        </div>
                        <div class="tab-pane fade" :class="{'show active': tab == 'usag'}"
                            id="usag-tab" role="tabpanel">
                            @include('host.meet.details.usag')
                        </div>

                        <div class="tab-pane fade" :class="{'show active': tab == 'verifications'}"
                            id="summary-tab" role="tabpanel">
                            @include('host.meet.details.verifications')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script>
        window.is_managed = {{ $_managed->id !== auth()->user()->id ? 'true' : 'false' }};
        window._managed_account = {{ $_managed->id }};
        window.gym_id = {{ $gym->id }};
        window.meet_id = {{ $meet->id }};
        let pdfDocumentImageUrl = "{{ asset('img/defaults/pdf_icon.png') }}";
        let docxDocumentImageUrl = "{{ asset('img/defaults/doc_icon.png') }}";
        let excelDocumentImageUrl = "{{ asset('img/defaults/xls_icon.png') }}";
        let defaultImage = "{{ asset('img/logos/logo.png') }}";
        let meetChartURL = "{{ url('meets') }}/";
    </script>
    <script src="{{ mix('js/host/meet/dashboard.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/admin/js/summernote.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ mix('js/host/meet/meet-dashboard.js') }}"></script>
    <script src="{{ mix('js/host/meet/meet_summary.js') }}"></script>

@endsection