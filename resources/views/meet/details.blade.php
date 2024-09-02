@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-calendar-check"></span> {{ $meet->name }}
@endsection
<style>
    .dd{
        background-color: #084c66;
        color: white;
        padding: 10px;
        margin: 5% 0%;
        border-radius: 2%;
    }
    a.btn.btn-default.active {
    background: #0a7a38;
    color: white;
}
</style>
@section('content-main')
    @include('include.errors')

    @if ($is_own)
        <div class="alert alert-info">
            <div class="font-weight-bold">
                <span class="fas fa-info-circle"></span>
                {{ $_managed->isNotCurrentUser() ? $_managed->first_name . ' is' : 'You\'re'}}
                the host of this meet.
            </div>
            <div>
                This meet is {{ $meet->is_published ? '' : 'NOT'}} published.
            </div>
            
            <div class="text-right">
                @if ($meet->canBeEdited() && ($_managed->isCurrentUser() || $_managed->pivot->can_edit_meet))
                    <div class="mb-2 mr-2 d-inline-block">
                        <a href="{{ route('gyms.meets.edit', ['gym' => $gym, 'meet' => $meet]) }}" 
                            class="btn btn-sm btn-success" title="Edit">
                                <span class="fas fa-edit"></span> Edit
                        </a>
                    </div>
                @endif

                @if ($meet->is_published && ($_managed->isCurrentUser() || $_managed->pivot->can_access_reports))
                    <div class="mb-2 mr-2 d-inline-block">
                        <a href="{{ route('host.meets.dashboard', ['gym' => $gym, 'meet' => $meet]) }}" 
                            class="btn btn-sm btn-primary" title="Dashboard">
                                <span class="fas fa-tachometer-alt"></span> Dashboard
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div>
        @if (($registrations != null) && ($registrations->count() > 0))
            <div class="alert alert-success">
                @foreach ($registrations as $registration)
                    <div class="d-flex flex-row flex-nowrap mb-1">
                        <div class="flex-grow-1">
                            <span class="fas fa-check-double"></span>
                            <strong>{{ $registration->gym->name }}</strong>
                            has registered for this meet.
                        </div>
                        <div>
                            <a href="{{ route('gyms.registration', ['gym' => $registration->gym, 'registration' => $registration]) }}"
                                class="btn btn-sm btn-info" title="View">
                                <span class="fas fa-eye"></span> View
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        @if($meet->id == 168)
        <div class="alert alert-info">
            <div class="d-flex flex-row flex-nowrap">
                <div class="flex-grow-1">
                    <span class="fas fa-info-circle"></span>
                    Having trouble registering event specialists in a level different from their AA level? 
                    <a class="btn btn-success" href="https://allgymnastics.zendesk.com/hc/en-us/articles/17671901030937-Registering-USAIGC-Event-Specialists">
                        Click here for instructions
                    </a>
                </div>
            </div>
        </div>
        @endif
        <div class="alert alert-primary">
            <div class="d-flex flex-row flex-nowrap">
                <div class="flex-grow-1">
                    <span class="fas fa-info-circle"></span>
                    @switch($meet->registrationStatus())
                        @case(\App\Models\Meet::REGISTRATION_STATUS_OPENING_SOON)
                            This meet will be open for registrations soon.
                            @break
        
                        @case(\App\Models\Meet::REGISTRATION_STATUS_OPEN)
                            This meet is open for registrations.
                            @break
        
                        @case(\App\Models\Meet::REGISTRATION_STATUS_LATE)
                            This is open for late registrations.
                            @break
        
                        @default
                            Registration is closed for this meet.
                            @if ($today < $meet->start_date)
                                You may enter a waitlist.
                            @endif
                    @endswitch
                </div>
                <div>
                    @if ($meet->is_published && (
                        ($registrations == null) ||
                        ($registrations->count() < $_managed->gyms()->count())
                    ))
                        @switch($meet->registrationStatus())
                            @case(\App\Models\Meet::REGISTRATION_STATUS_OPEN)
                                <div class="mb-2 mr-2 d-inline-block">
                                    <a href="{{ route('gyms.meets.register', ['meet' => $meet]) }}"
                                        class="btn btn-sm btn-success" title="Register">
                                            <span class="fas fa-sign-in-alt"></span> Register
                                    </a>
                                </div>
                                @break
        
                            @case(\App\Models\Meet::REGISTRATION_STATUS_LATE)
                                <div class="mb-2 mr-2 d-inline-block">
                                    <a href="{{ route('gyms.meets.register', ['meet' => $meet]) }}"
                                        class="btn btn-sm btn-warning" title="Late Registration">
                                            <span class="fas fa-clock"></span> Late Registration
                                    </a>
                                </div>
                                @break
        
                            @case(\App\Models\Meet::REGISTRATION_STATUS_CLOSED)
                                @if ($today < $meet->start_date)
                                    <div class="mb-2 mr-2 d-inline-block">
                                        <a href="{{ route('gyms.meets.register', ['meet' => $meet]) }}"
                                            class="btn btn-sm btn-warning" title="Waitlist Registration">
                                                <span class="fas fa-clock"></span> Enter Waitlist
                                        </a>
                                    </div>
                                @endif
                                @break
        
                        @endswitch
                    @endif
        
                    <div class="mb-2 d-inline-block">
                        <a href="{{ url()->previous() }}"
                            class="btn btn-sm btn-secondary">
                            <span class="fas fa-long-arrow-alt-left"></span> Back
                        </a>
                    </div>
                </div>
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

                
                <div style="margin-top:10px;">
                    
                    <h6><b><span class="fas fa-fw fa-qrcode"></span> Register With QR Code</b></h6>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=https://www.allgymnastics.com/meet-details?meet={{$meet->id}}" alt="" class="qr-code img-thumbnail img-responsive">
                    <!-- <img src="https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl=https://www.allgymnastics.com//meet-details/?meet={{$meet->id}}" alt="" class="qr-code img-thumbnail img-responsive"> -->
                </div>
                @if ($meet->is_published)
                <div class="form-group mb-0 mt-3 border-bottom">
                    <label for="meet-public-url" class="font-weight-bold mb-0">
                        <span class="fas fa-link"></span> Meet's public URL
                    </label>

                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <span class="fas fa-link"></span>
                            </span>
                        </div>
                        <input type="text" class="form-control bg-light" id="meet-public-url" readonly
                            value="https://www.allgymnastics.com/meet-details/?meet={{$meet->id}}">

                        <div class="input-group-append">
                            <button class="btn btn-info" type="button"
                                id="meet-public-url-copy" data-clipboard-target="#meet-public-url">
                                <span class="fas fa-copy"></span> Copy
                            </button>
                        </div>
                    </div>

                    <div class="mt-1 mb-1 text-right"
                        id="meet-public-url-copy-success-message" style="visibility: hidden">
                        <span class="badge badge-dark">
                            <span class="fas fa-check-circle"></span>
                            <span id="meet-public-url-copy-success"></span>
                        </span>
                    </div>
                </div>
                    <!-- @include('include.meet.sidebar_info') -->
                @endif
            </div>

            <div class="col">
                <div class="text-info small mb-3">
                    <span class="fas fa-info-circle"></span> All dates and times are in EST.
                </div>
                <div>
                    @include('include.meet.sidebar_info')
                </div>
                <nav class="navbar navbar-light bg-dark mb-5 mt-3" style="color:white;">
                    <div class="container-fluid">
                        <ul class="nav nav-default "  id="view-meet-list-tabs">
                            <li class="nav-item">
                                <a class="btn btn-default active" style="color:white;" data-toggle="tab" href="#general-tab" role="tab">
                                    <span class="fas fa-fw fa-align-justify"></span> General
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="btn btn-default" style="color:white;" data-toggle="tab" href="#registration-tab" role="tab">
                                    <span class="fas fa-fw fa-money-check"></span> Registration &amp; Payment
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-default" style="color:white;" data-toggle="tab" href="#competition-tab" role="tab">
                                    <span class="fas fa-fw fa-cogs"></span> Competition Settings
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-default" style="color:white;" data-toggle="tab" href="#schedule-tab" role="tab">
                                    <span class="fas fa-fw fa-file-alt"></span> Schedule &amp; Attachments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="btn btn-default" style="color:white;" data-toggle="tab" href="#contacts-tab" role="tab">
                                    <span class="fas fa-fw fa-address-book"></span> Contact
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="general-tab" role="tabpanel">
                        @include('meet.details.1')
                    </div>
                    <div class="tab-pane fade" id="registration-tab" role="tabpanel">
                        @include('meet.details.2')
                    </div>
                    <div class="tab-pane fade" id="competition-tab" role="tabpanel">
                        @include('meet.details.3')
                    </div>
                    <div class="tab-pane fade" id="schedule-tab" role="tabpanel">
                        @include('meet.details.4')
                    </div>
                    <div class="tab-pane fade" id="contacts-tab" role="tabpanel">
                        @include('meet.details.5')
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts-main')
    <script src="{{ mix('js/meet/meet-details.js') }}"></script>
@endsection


