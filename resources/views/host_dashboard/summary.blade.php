<div class="row">
    <div class="col-xl-3 col-md-6 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-university fa-fa-setting"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Money Earned</span>
                <span class="info-box-number">&#36; {{ number_format($summaryData['total_earn'], 2) }}</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-running fa-fa-setting"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Athletes</span>
                <span class="info-box-number">{{ $summaryData['total_ath'] }} Athletes</span>
                <span class="info-box-summary"><a class="text-decoration-none" href="" data-toggle="modal"
                                                  data-target="#total-athletes-summary-model">View Summary</a></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-bullhorn fa-fa-setting"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Coaches</span>
                <span class="info-box-number">{{ $summaryData['total_coa'] }} Coaches</span>
                <span class="info-box-summary"><a class="text-decoration-none" href="" data-toggle="modal"
                                                  data-target="#total-coaches-summary-model">View Summary</a></span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-users fa-fa-setting"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Gyms</span>
                <span class="info-box-number">{{ $summaryData['total_gym'] }} Gyms</span>
                <span class="info-box-summary"><a class="text-decoration-none" href="" data-toggle="modal"
                                                  data-target="#total-gym-summary-model">View Summary</a></span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="total-athletes-summary-model" tabindex="-1" role="dialog"
     aria-labelledby="modal-check-sending-details" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fas fas fa-running">&nbsp;&nbsp;</span><b>Meet Athletes Summary</b>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            <div class="p-2">
                <table class="table table-bordered" id="athleteLevelSummaryTbl">
                    <tbody>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Levels</th>
                        <th class="text-center">Total Athletes</th>
                    </tr>
                    @if(count($summaryData['athleteLevelArr']) > 0)
                        @foreach($summaryData['athleteLevelArr'] as $level => $count)
                            <tr>
                                <td class="text-center">{{$loop->iteration}}</td>
                                <td>{{$level}}</td>
                                <td class="text-center">{{$count}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="text-right m-3">
                <a class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="total-coaches-summary-model" tabindex="-1" role="dialog"
     aria-labelledby="modal-check-sending-details" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fas fas fa-bullhorn">&nbsp;&nbsp;</span><b>Meet Coaches Summary</b>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            <div class="p-2">
                <table class="table table-bordered" id="coachSummaryTbl">
                    <tbody>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Club Name</th>
                        <th class="text-center">Total Coaches</th>
                    </tr>
                    @if(count($summaryData['coachSummaryArr']) > 0)
                        @foreach($summaryData['coachSummaryArr'] as $coachKey => $coachArr)
                            <tr>
                                <td class="text-center">{{$loop->iteration}}</td>
                                @foreach($coachArr['gym'] as $gymName => $coachCount)
                                    <td>{{$gymName}}<br>
                                        <b>Coaches:</b>
                                        @if(count($coachArr['coach']) > 0)
                                            @foreach($coachArr['coach'] as $coach)
                                                {{ $coach }}@if (!$loop->last),@endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td class="text-center">{{$coachCount}}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="text-right m-3">
                <a class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="total-gym-summary-model" tabindex="-1" role="dialog"
     aria-labelledby="modal-check-sending-details" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="fas fas fa-users">&nbsp;&nbsp;</span><b>Meet Gym Summary</b>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="fas fa-times" aria-hidden="true"></span>
                </button>
            </div>
            <div class="p-2">
                <table class="table table-bordered" id="gymSummaryTbl">
                    <tbody>
                    <tr>
                        <th style="width: 10px">#</th>
                        <th>Gym Name</th>
                        <th style="width: 15%">Phone</th>
                        <th>Email</th>
                    </tr>
                    @if(count($summaryData['gymSummaryArr']) > 0)
                        @foreach($summaryData['gymSummaryArr'] as $gymData)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$gymData['gym']->name}}<br>
                                    <b>Coaches:</b>
                                    @if(count($gymData['coach']) > 0)
                                        @foreach($gymData['coach'] as $coach)
                                            {{ $coach }}@if (!$loop->last),@endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>{{$gymData['gym']->office_phone}}</td>
                                <td>{{$gymData['gym']->user->email}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
            <div class="text-right m-3">
                <a class="btn btn-secondary" data-dismiss="modal" aria-label="Close">Cancel</a>
            </div>
        </div>
    </div>
</div>

