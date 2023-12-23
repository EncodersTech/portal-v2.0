<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<style>
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
</style>
<body>
<div class="header">
    <div class="header-text">
        <h1 class="mb-0">
            Meet Coaches Summary
        </h1>
        <h4 class="mb-0">
            Date: {{ now()->format(Helper::AMERICAN_FULL_DATE_TIME) }}
        </h4>
    </div>
    <div class="logo-container">
        @include('PDF.host.meet.reports.common_logo_image')
    </div>
</div>
<div>
    <span style="font-size: 30px">Meet Name: &nbsp;<b>{{ $meet->name }}</b></span>
</div>
<table class="table-0 table-striped">
    <thead>
    <tr style="font-size: 28px">
        <th style="width: 5%">#</th>
        <th>Club Name</th>
        <th style="width: 17%; text-align: center">Total Coaches</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalCoach = 0;
    @endphp
    @if(count($coachSummaryArr) > 0)
        @foreach($coachSummaryArr as $coachKey => $coachArr)
            <tr style="font-size: 25px">
                <td>{{$loop->iteration}}</td>
                @foreach($coachArr['gym'] as $gymName => $coachCount)
                    <td>{{$gymName}}<br>
                        <b>Coaches:</b>
                        @if(count($coachArr['coach']) > 0)
                            @foreach($coachArr['coach'] as $coach)
                                {{ $coach }}@if (!$loop->last),@endif
                            @endforeach
                        @endif
                    <td class="text-center" style="text-align: center">{{$coachCount}}</td>
                    @php
                      $totalCoach += $coachCount;
                    @endphp
                @endforeach
            </tr>
        @endforeach
    @endif
    <tr>
        <td colspan="2">
            <strong style="font-size: 28px">Total</strong>
        </td>
        <td style="font-size: 28px; text-align: center">
            <strong> {{$totalCoach}} </strong>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
