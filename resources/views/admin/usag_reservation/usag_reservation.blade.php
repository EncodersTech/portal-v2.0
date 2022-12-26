@forelse($usagReservations as $usagReservation)
    <div class="col-12 col-xs-12 col-sm-4 col-md-12 col-lg-4 mb-1">
        <div class="small-box {{ \App\Models\USAGReservation::actionColor($usagReservation->action) }}">
            <div class="inner">
                <h5 class="mb-3">
                    @if($usagReservation->action == \App\Models\USAGReservation::RESERVATION_ACTION_ADD)
                        <span class="fas fa-fw fa-plus-square"></span>
                        New USAG Reservation
                    @else
                        <span class="fas fa-fw fa-pen-square"></span>
                        USAG Reservation Update
                    @endif
                </h5>
                <div class="" style="letter-spacing: 0.5px">
                    <div class="">
                        <strong>Sanction No.:</strong> {{ $usagReservation->usag_sanction->number }}
                    </div>
                    <div class="">
                        <strong>Gym:</strong> {{ $usagReservation->gym->name }}
                    </div>
                    <div class="">
                        <strong>Meet:</strong>
                        <span>
                            {{ $usagReservation->usag_sanction->usag_meet_name }}
                        </span>
                    </div>
                    <div class="">
                        <strong>Category:</strong> {{ $usagReservation->usag_sanction->level_category->name }}
                    </div>
                    <div class="">
                        <strong>Type:</strong>
                        @if($usagReservation->action == \App\Models\USAGReservation::RESERVATION_ACTION_ADD)
                            <span class="badge badge-pill badge-light">New Sanction</span>
                        @elseif($usagReservation->action == \App\Models\USAGReservation::RESERVATION_ACTION_UPDATE)
                            <span class="badge badge-pill badge-warning">Details Updated</span>
                        @elseif($usagReservation->action == \App\Models\USAGReservation::RESERVATION_ACTION_SCRATCH)
                            <span class="badge badge-pill badge-info">Sanction Removed</span>
                        @else
                            <span class="badge badge-pill badge-dark">Vendor Change</span>
                        @endif
                    </div>
                    <div class="">
                        <strong>Last
                            updated:</strong> {{ \Carbon\Carbon::parse($usagReservation->timestamp)->format('m/d/y h:i:s a') }}
                    </div>
                </div>
            </div>
            <div class="text-right small-box-footer d-flex">
                <span class="ml-auto usagHide" id="usagReservationHide" data-id="{{$usagReservation->id}}">
                    @if($usagReservation->status == \App\Models\USAGReservation::RESERVATION_STATUS_HIDE)
                        <i class="fas fa-eye-slash"></i>
                    @else
                        <i class="fas fa-eye"></i>
                    @endif
                </span>
                <a href="javascript:void(0);" class="usagDelete" id="usagReservationDelete" data-id="{{$usagReservation->id}}">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
        </div>
    </div>
@empty
    <h3 style="width:450px; margin:0 auto;">No USAG Reservations available.</h3>
@endforelse


<div class="mt-0 mb-5 col-12 usagReservationPagination">
    <div class="row paginatorRow">
        <div class="col-lg-2 col-md-6 col-sm-12 pt-2">
            @if(count($usagReservations) > 0)
                <span class="d-inline-flex">
                    Showing
                    <span class="font-weight-bold ml-1 mr-1">{{ $usagReservations->firstItem() }}</span> -
                    <span class="font-weight-bold ml-1 mr-1">{{ $usagReservations->lastItem() }}</span> of
                    <span class="font-weight-bold ml-1">{{ $usagReservations->total() }}</span>
                </span>
            @endif
        </div>
        <div class="col-lg-10 col-md-6 col-sm-12 d-flex justify-content-end">
            {{ $usagReservations->links() }}
        </div>
    </div>
</div>
