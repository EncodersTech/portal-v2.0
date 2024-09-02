@extends('layouts.main')

@section('content-header')
    <span class="fas fa-fw fa-tachometer-alt"></span> Hello, {{ Auth::user()->first_name }} !

    <span class="float-right">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#popup">
            <span class="fas fa-fw fa-info-circle"></span> Latest Updates
        </button>
    </span>
@endsection
<style>
    .small-box {
        border-radius: .25rem;
        box-shadow: 0 0 10px rgba(0, 0, 0, .12), 0 3px 3px rgba(0, 0, 0, .20);
        display: block;
        margin-bottom: 20px;
        position: relative;
    }

    .bg-success, .bg-success > a {
        color: #fff !important;
    }

    .small-box > .inner {
        padding: 10px;
    }

    @media (min-width: 1200px) {
        .col-lg-3 .small-box h3, .col-md-3 .small-box h3, .col-xl-3 .small-box h3 {
            font-size: 2.2rem;
        }
    }

    @media (min-width: 992px) {
        .col-lg-3 .small-box h3, .col-md-3 .small-box h3, .col-xl-3 .small-box h3 {
            font-size: 1.6rem;
        }
    }

    .small-box h3 {
        font-size: 2.2rem;
        font-weight: 700;
        margin: 0 0 10px;
        padding: 0;
        white-space: nowrap;
    }
    .small-box>.small-box-footer {
        background-color: rgba(0,0,0,.1);
        color: rgba(255,255,255,.8);
        display: block;
        padding: 3px 0;
        position: relative;
        text-align: center;
        text-decoration: none;
        z-index: 10;
    }
    .small-box-footer-font{
        font-size: 0.7rem;
        padding: 3px !important;
    }

    .small-box h3, .small-box p {
        z-index: 5;
    }
    .savings-info {
        position: relative;
        animation-name: excitement;
        animation-duration: 2s;
        animation-timing-function: ease-out;
        animation-delay: 1s;
        animation-fill-mode: forwards;
    }

@keyframes excitement {
  0% {
    transform: scale(1);
  }
  25% {
    transform: scale(1.2);
  }
  50% {
    transform: scale(1);
  }
  75% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

</style>

@section('content-main')
    @if ($_managed->isNotCurrentUser())
        <h6 class="secondary-title font-weight-bold ml-3">
            <span class="fas fa-fw fa-info-circle"></span>
            You are managing {{ $_managed->first_name }}'s account
        </h6>
    @endif

    <!-- popup modal start -->
    <div class="modal fade" id="popup" tabindex="-1" role="dialog" aria-labelledby="popup" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Latest Updates</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @foreach($generalNotifications as $notification)
                        @if ($notification->is_selected_users == true && !in_array($_managed->id, array_keys(json_decode($notification->selected_users, true))))
                            @continue
                        @endif
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <button class="btn btn-primary" type="button" onclick="openDataDiv(<?= $notification->id ?>)">
                                    <strong>{{ $notification->title }}  </strong>
                                    <span class="fas fa-caret-down"></span>
                                </button>
                            </div>
                            <div class="card-body" id="notificationDataDiv_<?= $notification->id ?>" style="{{ $loop->first ? '':'display:none;'  }}">
                                <p>{!! $notification->content !!}</p>
                            </div>
                            <div class="card-footer text-right">
                                <small class="text-muted">
                                    <span class="fas fa-fw fa-calendar"></span>
                                    {{ Carbon\Carbon::parse($notification->created_at)->format('m/d/Y')  }}
                                </small>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- popup modal end -->

    <div class="content-main p-3">
        <div class="row">
            <div class="col">
                <div class="alert alert-info">
                    <strong>
                        <span class="fas fa-fw fa-info-circle"></span>
                        USA Gymnastics Sanctions and Reservations
                    </strong> <br/>
                    To be able to import meets or registrations made on USAG's website,
                    please make sure you input your correct USAG membership number.
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <div class="card-group">
                    <div class="card">
                        <div class="card-header bg-primary text-light">
                            <span class="fas fa-calendar-alt"></span> Browse and Register for Meets
                        </div>
                        <div class="card-body bg-white pb-0">
                            Search for upcoming meets that your athletes can compete in.
                        </div>
                        <div class="card-footer text-right border-top-0 bg-white pt-0">
                            <a href="{{ route('meets.browse') }}" class="btn btn-primary">
                                <span class="fas fa-calendar-alt"></span> Browse
                            </a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-primary text-light">
                            <span class="fas fa-dumbbell"></span> My Gym Information
                        </div>
                        <div class="card-body bg-white pb-0">
                            <p>
                                View and modify your gymnastics club(s) information. <br>
                                Please note, you must add your
                                club's information and number in order to register for competitions.
                            </p>
                        </div>
                        <div class="card-footer text-right border-top-0 bg-white pt-0">
                            <a href="{{ route('gyms.create') }}" class="btn btn-success">
                                <span class="fas fa-plus"></span> Create
                            </a>

                            <a href="{{ route('gyms.index') }}" class="btn btn-primary ml-1">
                                <span class="fas fa-dumbbell"></span> Manage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($showSanctionNotifications)
            <ag-sanction-notifications class="mb-3" :managed="{{ $_managed->id }}"></ag-sanction-notifications>
            <ag-reservations-notifications :managed="{{ $_managed->id }}"></ag-reservations-notifications>
        @endif
    </div>
@endsection
<!-- c -->
@section('scripts-main')
    <script>
        window.show_sanction_notifications = {{ $showSanctionNotifications ? 'true' : 'false' }};
        var has_popup = {{ $has_popup }}; // true if there are notifications to show in the popup
        $(document).ready(function() {
            if (has_popup) {
                // show the popup after 1 second
                setTimeout(function() {
                    $('#popup').modal('show');
                }, 1200); 
            }
        });
        openDataDiv = function(id){
            var x = document.getElementById("notificationDataDiv_"+id);
            var caret_span = x.previousElementSibling.querySelector('span');
            console.log(caret_span);
            if (x.style.display === "none") {
                x.style.display = "block";
                caret_span.classList.add('fa-caret-down');
                caret_span.classList.remove('fa-caret-right'); 
            } else {
                x.style.display = "none";
                caret_span.classList.add('fa-caret-right');
                caret_span.classList.remove('fa-caret-down');
            }
        }

    </script>

    @if ($showSanctionNotifications)
       <script src="{{ mix('js/include/sanction-notifications.js') }}"></script>
    @endif
@endsection
