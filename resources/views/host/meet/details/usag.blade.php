<div class="mt-2">
    @if(isset($usagSanctions) && (count($usagSanctions) > 0 || count($meet->usag_reservations) > 0))
    @if(count($usagSanctions) > 0)
    <h5 class="pb-1 border-bottom"><span class="fas fa-fw fa-cloud-download-alt"></span> USAG Sanction</h5>
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-merged-tab" data-toggle="tab" href="#mSanction" role="tab"
                aria-controls="nav-home" aria-selected="true">Merged</a>
            <a class="nav-item nav-link" id="nav-pending-tab" data-toggle="tab" href="#pSanction" role="tab"
                aria-controls="nav-profile" aria-selected="false">Pending</a>
            <a class="nav-item nav-link" id="nav-dismissed-tab" data-toggle="tab" href="#duSanction" role="tab"
                aria-controls="nav-profile" aria-selected="false">Dismissed / Unassigned</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="mSanction" role="tabpanel" aria-labelledby="nav-merged-tab">
            <div class="row">
                <div class="col-12 mb-3" style="{{(count($usagSanctions)>10)?'height: 500px':''}} overflow-y: scroll">
                    <table class="tableFixHead table table-bordered table-striped" id="usagSanctionTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Sanction No</th>
                                <th scope="col">Gym</th>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usagSanctions as $key => $usagSanction)
                            <tr>
                                @if($usagSanction->status == \App\Models\USAGSanction::SANCTION_STATUS_MERGED)
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $usagSanction->number }}</td>
                                <td>{{ $usagSanction->gym->name }}</td>
                                <td>@if($usagSanction->level_category){{ $usagSanction->level_category->name }} @else
                                    {{'N/A'}} @endif</td>
                                <td>{{ $usagSanction->action_status }}</td>
                                <td class="{{\App\Models\USAGSanction::statusColor($usagSanction->status)}}">
                                    {{ $usagSanction->status_label }}</td>
                                <td>{{ $usagSanction->timestamp }}</td>
                                @endif
                            </tr>
                            @empty
                            <th colspan="7" class="text-center">No Records Found.</th>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="pSanction" role="tabpanel" aria-labelledby="nav-pending-tab">
            <div class="row">
                <div class="col-12 mb-3" style="{{(count($usagSanctions)>10)?'height: 500px':''}} overflow-y: scroll">
                    <table class="tableFixHead table table-bordered table-striped" id="usagSanctionTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Sanction No</th>
                                <th scope="col">Gym</th>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usagSanctions as $key => $usagSanction)
                            <tr>
                                @if($usagSanction->status == \App\Models\USAGSanction::SANCTION_STATUS_PENDING)
                                <td scope="row">{{ $loop->iteration }}</td>
                                <td>{{ $usagSanction->number }}</td>
                                <td>{{ $usagSanction->gym->name }}</td>
                                <td>@if($usagSanction->level_category){{ $usagSanction->level_category->name }} @else
                                    {{'N/A'}} @endif</td>
                                <td>{{ $usagSanction->action_status }}</td>
                                <td class="{{\App\Models\USAGSanction::statusColor($usagSanction->status)}}"><a
                                        href="{{route('gyms.sanctions.usag',['gyms'=>$usagSanction->gym_id,'sanctions'=>$usagSanction->number])}}">{{ $usagSanction->status_label }}</a>
                                </td>
                                <td>{{ $usagSanction->timestamp }}</td>
                                @else
                                @if($loop->iteration == 1)
                                <th colspan="7" class="text-center">No Records Found.</th>
                                @endif
                                @endif
                            </tr>
                            @empty
                            <th colspan="7" class="text-center">No Records Found.</th>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="duSanction" role="tabpanel" aria-labelledby="nav-dismissed-tab">
            <div class="row">
                <div class="col-12 mb-3" style="{{(count($usagSanctions)>10)?'height: 500px':''}} overflow-y: scroll">
                    <table class="tableFixHead table table-bordered table-striped" id="usagSanctionTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Sanction No</th>
                                <th scope="col">Gym</th>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usagSanctions as $key => $usagSanction)
                            <tr>
                                @if($usagSanction->status == \App\Models\USAGSanction::SANCTION_STATUS_DISMISSED ||
                                $usagSanction->status == \App\Models\USAGSanction::SANCTION_STATUS_UNASSIGNED)
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $usagSanction->number }}</td>
                                <td>{{ $usagSanction->gym->name }}</td>
                                <td>@if($usagSanction->level_category){{ $usagSanction->level_category->name }} @else
                                    {{'N/A'}} @endif</td>
                                <td>{{ $usagSanction->action_status }}</td>
                                <td class="{{\App\Models\USAGSanction::statusColor($usagSanction->status)}}">
                                    {{ $usagSanction->status_label }}</td>
                                <td>{{ $usagSanction->timestamp }}</td>
                                @else
                                @if($key == 0)
                                <th colspan="7" class="text-center">No Records Found.</th>
                                @endif
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(count($meet->usag_reservations) > 0 )
    <br>
    <h5 class="pb-1 border-bottom"><span class="fas fa-fw fa-cloud-download-alt"></span> USAG Reservation</h5>
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-item nav-link active" id="nav-rmerged-tab" data-toggle="tab" href="#mReservation" role="tab"
                aria-controls="nav-home" aria-selected="true">Merged</a>
            <a class="nav-item nav-link" id="nav-rpending-tab" data-toggle="tab" href="#pReservation" role="tab"
                aria-controls="nav-profile" aria-selected="false">Pending</a>
            <a class="nav-item nav-link" id="nav-runassigned-tab" data-toggle="tab" href="#duReservation" role="tab"
                aria-controls="nav-profile" aria-selected="false">Dismissed / Unassigned</a>
        </div>
    </nav>
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="mReservation" role="tabpanel" aria-labelledby="nav-rmerged-tab">
            <div class="row">
                <div class="col-12 mb-3" style="height: 500px; overflow-y: scroll">
                    <table class="tableFixHead table table-bordered table-striped" id="usagReservationTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Sanction No</th>
                                <th scope="col">Gym</th>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($meet->usag_reservations->where('status',\App\Models\USAGReservation::RESERVATION_STATUS_MERGED)->sortByDesc('timestamp')
                            as $key => $usagReservation)
                            <tr>
                                <th scope="row">{{ $loop->iteration }}</th>
                                <td>{{ $usagReservation->usag_sanction->number }}</td>
                                <td>@if($usagReservation->gym){{ $usagReservation->gym->name }} @else {{'N/A'}} @endif
                                </td>
                                <td>@if($usagReservation->usag_sanction){{ $usagReservation->usag_sanction->level_category->name }}
                                    @else {{'N/A'}} @endif</td>
                                <td>{{ $usagReservation->action_status }}</td>
                                <td class="{{\App\Models\USAGReservation::statusColor($usagReservation->status)}}">
                                    {{ $usagReservation->status_label }}</td>
                                <td>{{ $usagReservation->timestamp }}</td>
                            </tr>
                            @empty
                            <th colspan="7" class="text-center">No Records Found.</th>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pReservation" role="tabpanel" aria-labelledby="nav-rpending-tab">
            <div class="row">
                <div class="col-12 mb-3" style="height: 500px; overflow-y: scroll">
                    <table class="tableFixHead table table-bordered table-striped" id="usagReservationTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Sanction No</th>
                                <th scope="col">Gym</th>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last updated</th>
                                <th scope="col">Details</th>
                                <th scope="col">Mail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meet->usag_reservations->where('status',\App\Models\USAGReservation::RESERVATION_STATUS_PENDING)->sortByDesc('timestamp')
                            as $key => $usagReservation)
                            <tr>
                                @if($usagReservation->status == \App\Models\USAGReservation::RESERVATION_STATUS_PENDING)
                                <th scope="row">{{ $loop->iteration }} </th>
                                <td>{{ $usagReservation->usag_sanction->number }}</td>
                                <td>@if($usagReservation->gym){{ $usagReservation->gym->name }} @else {{'N/A'}} @endif
                                </td>
                                <td>@if($usagReservation->usag_sanction){{ $usagReservation->usag_sanction->level_category->name }}
                                    @else {{'N/A'}} @endif</td>
                                <td>{{ $usagReservation->action_status }}</td>
                                <td class="{{\App\Models\USAGReservation::statusColor($usagReservation->status)}}">
                                    {{ $usagReservation->status_label }}</td>
                                <td>{{ $usagReservation->timestamp }}</td>
                                <td>
                                    <a href="{{env('APP_URL')}}/gyms/{{$usagReservation->gym->id}}/sanctions/usag/{{$usagReservation->usag_sanction->number}}/reservation" class="btn btn-sm btn-success ml-1">View Details</a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info ml-1" title="Send email"
                                        @click="sendEmailTo({{$usagReservation}})">
                                        <span class="fas fa-fw fa-envelope"></span>
                                </button>
                                </td>
                                @else
                                <th colspan="7" class="text-center">No Records Found.</th>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="duReservation" role="tabpanel" aria-labelledby="nav-runassigned-tab">
            <div class="row">
                <div class="col-12 mb-3" style="height: 500px; overflow-y: scroll; width: 0px;">
                    <table class="tableFixHead table table-bordered table-striped" id="usagReservationTable">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Sanction No</th>
                                <th scope="col">Gym</th>
                                <th scope="col">Contact</th>
                                <th scope="col">Category</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Last updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $kl = 0; $it = 1; ?>
                            @foreach($meet->usag_reservations->sortByDesc('timestamp') as $key => $usagReservation)
                            <?php 
                                $kl = 1;

                                $payload = $usagReservation->payload;
                                $clubName = $payload['Reservation']['ClubName'];
                                $contact = (isset($payload['Reservation']['ClubContact'])?'<b>Name: </b>'.$payload['Reservation']['ClubContact'].'<br>':'') 
                                            . (isset($payload['Reservation']['ClubContactEmail'])?'<b>Email: </b>'.$payload['Reservation']['ClubContactEmail'].'<br>':'')
                                            . (isset($payload['Reservation']['ClubContactPhone'])?'<b>Phone: </b>'.$payload['Reservation']['ClubContactPhone'].'<br>':'')
                                            . (isset($payload['Reservation']['ClubUSAGID'])?'<b>USAG ID: </b>'.$payload['Reservation']['ClubUSAGID']:'');
                            ?>
                            <tr>
                                @if($usagReservation->status ==
                                \App\Models\USAGReservation::RESERVATION_STATUS_DISMISSED || $usagReservation->status ==
                                \App\Models\USAGReservation::RESERVATION_STATUS_UNASSIGNED )
                                <th scope="row">{{ $it++ }}</th>
                                <td>{{ $usagReservation->usag_sanction->number }}</td>
                                <td>@if($usagReservation->gym){{ $usagReservation->gym->name }} @else {{$clubName}}
                                    @endif
                                </td>
                                <td><?php echo $contact; ?></td>
                                <td>@if($usagReservation->usag_sanction){{ $usagReservation->usag_sanction->level_category->name }}
                                    @else {{'N/A'}} @endif</td>
                                <td>{{ $usagReservation->action_status }}</td>
                                <td class="{{\App\Models\USAGReservation::statusColor($usagReservation->status)}}">
                                    {{ $usagReservation->status_label }}</td>
                                <td>{{ $usagReservation->timestamp }}</td>
                                @else
                                @if($kl == 0)
                                <th colspan="7" class="text-center">No Records Found.</th>
                                @endif
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
    @else
    <h4>No USAG Reservation.</h4>
    @endif
</div>