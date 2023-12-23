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
    .adminMeetTables tr th {
        text-align: center;
        vertical-align: middle !important;
    }
    tr:nth-child(even) {background-color: #f2f2f2;}
</style>
<body>
<div class="header">
    <div class="header-text">
        <h1 class="mb-0">
            Pending Withdrawal Balance Report
        </h1>
        <h4 class="mb-0">
            Date:  {{ now()->format(Helper::AMERICAN_FULL_DATE_TIME) }}
        </h4>
    </div>
    <div class="logo-container">
        @include('PDF.host.meet.reports.common_logo_image')
    </div>
</div>
@if ($users->count() < 1)
    No data found.
@else
    <table class="table-0 full-width adminMeetTables">
        <thead>
        <tr>
            <th class="col" style="width: 1%">#</th>
            <th class="col" style="width: 4%">Name</th>
            <th class="col" style="width: 8%">Email</th>
            <th class="col" style="width: 4%">Phone</th>
            <th class="col" style="width: 2%">Balance</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($users as $key => $user)
            <tr>
                <td class="col">
                    {{ $loop->iteration  }}
                </td>
                <td class="col">
                    {{$user->fullName()}}
                </td>
                <td class="col">
                    {{$user->email}}
                </td>
                <td class="col">
                    {{$user->office_phone}}
                </td>
                <td class="col">
                    {{number_format($user->cleared_balance),2}}
                </td>
            </tr>
        @endforeach
        <tr>
            <th colspan="3"></th>
            <td><b>Total</b></td>
            <td><b>{{number_format($total_balance),2}}</b></td>
        </tr>
        </tbody>
    </table>
    <br>
@endif
</body>
</html>
