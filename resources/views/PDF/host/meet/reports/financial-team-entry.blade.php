<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
    <style>
        .text-center {
            text-align: center !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Team Entry Report
            </h1>
            <h2 class="mb-0">
                Meet: {{ $meet->name }}
                <br/>
                Date: {{ $meet->start_date->format(Helper::AMERICAN_FULL_DATE) }}
                <br/>
                Host: {{ $host->name }}
            </h2>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>

    @if ($meet->registrationStatus() != \App\Models\Meet::REGISTRATION_STATUS_CLOSED)
        <div class="text-danger mb-3">
            The information on this report is not final and might change at a later date.
            <strong>A final report can be obtained after this meet is closed for registrations.</strong>
        </div>
    @endif

    @if (count($registrations) < 1)
        No Registration.
    @else
        <table class="table-0 table-bordered table-sm">
            <thead>
                <tr>
                    <th class="col-4">Club</th>
                    <th class="col-2">Team Type</th>
                    <th class="col-4">Discipline</th>
                    <th class="col-5">Total Participants</th>
                    <th class="col-2">Gender</th>
                    <th class="col-2">Level</th>
                    <th class="col-5">Reg Count</th>
                    <th class="col-5">Team Count</th>
                    <th class="col-5">Scratch Count</th>
                    <th class="col-5">Fees</th>
                    <th class="col-5">Team Fees</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($registrations as $r)
                <tr>
                    <td rowspan="{{ $r['total_levels']+1 }}">
                        <strong>{{ $r['gym']->name }}</strong><br/>
                        <address>
                            <strong>Address: </strong>
                            {{ $r['gym']->addr_1 }}<br/>

                            @if ($r['gym']->addr_2)
                                {{ $r['gym']->addr_2 }}<br/>
                            @endif

                            {{ $r['gym']->city }}, {{ $r['gym']->state->code }},
                            {{ $r['gym']->zipcode }}<br/>
                            {{ $r['gym']->country->name }}
                        </address>
                        <strong>Phone:</strong> {{ $r['gym']->office_phone }}
                    </td>
                    <td rowspan="{{ $r['total_levels']+1 }}">
                        {{ $r['sanctioning_body'] }}
                    </td>
                    @php
                        $total_teams = 0;
                        $total_scratches = 0;
                        $total_fees = 0;
                        $total_team_fees = 0;
                    @endphp
                    @foreach ($r['discipline'] as $k=>$v)
                        <td class="text-center" rowspan="{{ count($v['level'])+1 }}">{{ $k }}</td>
                        <td class="text-center" rowspan="{{ count($v['level'])+1 }}">{{ $v['total_participants'] }}</td>
                        @foreach ($v['level'] as $l=>$lv)
                            <td class="text-center">{{ $lv['gender'] }}</td>
                            <td class="text-center">{{ $l }}</td>
                            <td class="text-center">{{ $lv['athlete_count'] }}</td>
                            <td class="text-center">{{ $lv['team_count'] }}</td>
                            <td class="text-center">{{ $lv['scratch_count'] }}</td>
                            <td class="text-center">{{ $lv['athlete_fees'] }}</td>
                            <td class="text-center">{{ $lv['team_fees'] }}</td>
                            @php
                                $total_teams += $lv['team_count'];
                                $total_scratches += $lv['scratch_count'];
                                $total_fees += $lv['athlete_fees'];
                                $total_team_fees += $lv['team_fees'];
                            @endphp
                            </tr>
                        @endforeach
                            <td class="text-center" colspan="2" style="background-color: #dbdbdb;"><b>Total</b></td>
                            <td style="background-color: #dbdbdb;" class="text-center">{{ $v['total_participants'] }}</td>
                            <td style="background-color: #dbdbdb;" class="text-center">{{$total_teams}}</td>
                            <td style="background-color: #dbdbdb;" class="text-center">{{$total_scratches}}</td>
                            <td style="background-color: #dbdbdb;" class="text-center">{{$total_fees}}</td>
                            <td style="background-color: #dbdbdb;" class="text-center">{{$total_team_fees}}</td>
                            </tr>
                    @endforeach
            @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
