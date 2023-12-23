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
            Meet Gym Summary
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
        <th>Gym Name</th>
        <th style="width: 20%">Phone</th>
        <th>Email</th>
    </tr>
    </thead>
    <tbody>
    @if(count($gymSummaryArr) > 0)
        @foreach($gymSummaryArr as $gymData)
            <tr style="font-size: 25px">
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
</body>
</html>
