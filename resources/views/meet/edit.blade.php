@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span> Editing {{ $meet->name }}
    <span class="small text-dark">
        {{ $_managed->isNotCurrentUser() ? '(on behalf of ' . $_managed->first_name .')' : ''}}
    </span>
@endsection

@section('content-main')
    @include('include.errors')

    <h5 class="secondary-title font-weight-bold ml-3">
        <span class="fas fa-fw fa-dumbbell"></span>
        {{ $gym->name }}
    </h5>

    <div class="alert alert-info">
        <span class="fas fa-info-circle"></span>
        This meet is {{ $meet->is_published ? '' : 'NOT'}} published.

        @if ($meet->is_published && !$meet->canBeUnpublished())
            This meet already has registrations and cannot be unpublished.
        @endif

        <div class="text-right">
            <div class="mb-2 mr-2 d-inline-block">
                @if ($meet->is_published)
                    <form method="POST" id="meet-unpublish-form" class="d-none"
                        action="{{ route('gyms.meets.unpublish', ['gym' => $gym, 'meet' => $meet]) }}">
                        @csrf
                    </form>
                    <button class="btn btn-sm btn-secondary" title="Unpublish" id="meet-unpublish-button"
                        {{ $meet->canBeUnpublished() ? '' : 'disabled' }}>
                            <span class="fas fa-eye-slash"></span> Unpublish
                    </button>
                @else
                    <form method="POST" id="meet-publish-form" class="d-none"
                        action="{{ route('gyms.meets.publish', ['gym' => $gym, 'meet' => $meet]) }}">
                        @csrf
                    </form>
                    <button class="btn btn-sm btn-warning" data-past="{{ $past_meets->count() }}" title="Publish" id="meet-publish-button">
                            <span class="fas fa-eye"></span> Publish
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="content-main p-3">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="">
                    <img id="profile-picture-display" src="{{ $meet->profile_picture }}"
                        class="rounded profile-picture-256" alt="Meet Picture">

                    <div class="pt-1">
                        <form class="d-inline-block mr-1" action="{{ route('gyms.meets.picture.reset', ['gym' => $gym, 'meet' => $meet]) }}"
                                method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-link p-0">
                                <span class="fas fa-fw fa-ban"></span> Clear
                            </button>
                        </form>

                        <form id="profile-picture-change-form"
                                class="d-inline-block" action="{{ route('gyms.meets.picture.change', ['gym' => $gym, 'meet' => $meet]) }}"
                                method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" tabindex="-1" name="meet_picture" id="profile-picture"
                                    class="invisible-file-input" accept="image/jpeg,image/png,image/jpg">
                        </form>
                        <button type="button" class="btn btn-sm btn-link p-0" id="profile-picture-change">
                            <span class="fas fa-fw fa-sync-alt"></span> Change
                        </button>
                    </div>

                    <div class="small text-dark">
                        {{ $profile_picture_max_size }} max.
                    </div>
                </div>
                <div class="mt-3">
                    <ul class="nav flex-column nav-pills" role="tablist" id="view-meet-list-tabs">
                        <li class="nav-item">
                            <a class="nav-link {{ $step == 1 ? 'active' : ''}}" data-toggle="tab" href="#general-tab" role="tab">
                                <span class="fas fa-fw fa-align-justify"></span> General
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ $step == 2 ? 'active' : ''}}" data-toggle="tab" href="#registration-tab" role="tab">
                                <span class="fas fa-fw fa-money-check"></span> Registration &amp; Payment
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $step == 3 ? 'active' : ''}}" data-toggle="tab" href="#competition-tab" role="tab">
                                <span class="fas fa-fw fa-cogs"></span> Competition Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $step == 4 ? 'active' : ''}}" data-toggle="tab" href="#schedule-tab" role="tab">
                                <span class="fas fa-fw fa-file-alt"></span> Schedule &amp; Attachments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $step == 5 ? 'active' : ''}}" data-toggle="tab" href="#contacts-tab" role="tab">
                                <span class="fas fa-fw fa-address-book"></span> Contact
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col">
                @if ($restricted_edit)
                    <div class="text-danger small" id="restricted-mode-enabled">
                        <strong><span class="fas fa-exclamation-triangle"></span> This meet has registrations.</strong>
                        You will not be able to change the values of some fields (fields have been disabled).
                    </div>
                @endif
                <div class="text-info font-weight-bold mb-3">
                    <span class="fas fa-info-circle"></span> Changes are only
                    saved when you click "Save" on their respective tab. All dates and times are in EST.
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade {{ $step == 1 ? 'show active' : ''}}" id="general-tab" role="tabpanel">
                        @include('meet.edit.1')
                    </div>
                    <div class="tab-pane fade {{ $step == 2 ? 'show active' : ''}}" id="registration-tab" role="tabpanel">
                        @include('meet.edit.2')
                    </div>
                    <div class="tab-pane fade {{ $step == 3 ? 'show active' : ''}}" id="competition-tab" role="tabpanel">
                        @include('meet.edit.3')
                    </div>
                    <div class="tab-pane fade {{ $step == 4 ? 'show active' : ''}}" id="schedule-tab" role="tabpanel">
                        @include('meet.edit.4')
                    </div>
                    <div class="tab-pane fade {{ $step == 5 ? 'show active' : ''}}" id="contacts-tab" role="tabpanel">
                        @include('meet.edit.5')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-show-past-meets" tabindex="-1" role="dialog"
         aria-labelledby="modal-show-past-meets" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title text-primary">
                        Past Meets
                    </h5>
                    <button type="button" class="close modal-show-past-meets-close" aria-label="Close">
                        <span class="fas fa-times" aria-hidden="true"></span>
                    </button>
                </div>
                <div id="modal-show-past-meets-errors" class="text-danger mt-1 ml-2" role="alert"></div>

                <div class="modal-body">

                    <form method="post" id="past-meets-form">
                        <input type="hidden" name="meet_id"  value="{{ $meet->id }}">
                        <div class="form-row">
                            <div id="modal-show-past-meets-element" class="w-100 ml-2">
                                @foreach($past_meets as $past_meet)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="meets[]"
                                               id="pastMeets-{{ $past_meet->id }}" value="{{ $past_meet->id }}">
                                        <label class="form-check-label" for="pastMeets-{{ $past_meet->id }}">
                                            {{ $past_meet->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="modal-footer pb-0 pr-0 mt-4">
                            <div class="text-right">
                                <button type="button" class="btn btn-primary send-mail">
                                <span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"
                                      id="modal-show-past-meets-spinner" style="display: none;">
                                </span>
                                    <span class="fas fa-plus"></span> Send Mail & Publish
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/meet/meet-edit.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#accept_mailed_check").change(function(e){
                var amc = $("#accept_mailed_check").is(":checked");
                if(amc == true)
                    $('#accept_deposit').prop("disabled",false);
                else{
                    $('#accept_deposit').prop("disabled",true);
                    $('#accept_deposit').prop("checked",false);
                }
            });
            $("#accept_deposit").change(function(e){
                var amc = $("#accept_deposit").is(":checked");
                if(amc == true)
                    $('#deposit_ratio').prop("disabled",false);
                else{
                    $('#deposit_ratio').prop("disabled",true);
                    $('#deposit_ratio').prop("checked",false);
                }
            });
        });
    </script>
@endsection
