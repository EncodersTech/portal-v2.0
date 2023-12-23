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
            Meet Athletes Summary
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
        <th>Levels</th>
        <th style="width: 17%">Total Athletes</th>
    </tr>
    </thead>
    <tbody>
    @php
        $totalAth = 0;
    @endphp
        @if(count($athleteLevelArr) > 0)
        @foreach($athleteLevelArr as $level => $count)
            <tr style="font-size: 25px">
                <td>{{$loop->iteration}}</td>
                <td>{{$level}}</td>
                <td style="text-align: center">{{$count}}</td>
                 @php
                      $totalAth += $count;
                 @endphp
            </tr>
        @endforeach
    @endif
        <tr>
            <td colspan="2">
                <strong style="font-size: 28px">Total</strong>
            </td>
            <td style="font-size: 28px; text-align: center">
                <strong> {{$totalAth}} </strong>
            </td>
        </tr>
    </tbody>
</table>
</body>
</html>
