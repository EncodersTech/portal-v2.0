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
            Meets Report
        </h1>
        <h4 class="mb-0">
            Date:  {{ now()->format(Helper::AMERICAN_FULL_DATE_TIME) }}
        </h4>
    </div>
    <div class="logo-container">
        @include('PDF.host.meet.reports.common_logo_image')
    </div>
</div>
@if ($meetLists->count() < 1)
    No Meets.
@else
    <table class="table-0 full-width adminMeetTables">
        <thead>
        <tr>
            <th class="col" style="width: 1%"></th>
            <th class="col" style="width: 5%">Meet Name</th>
            <th class="col" style="width: 5%">Club Name</th>
            <th class="col" style="width: 3%">Start Date</th>
            <th class="col" style="width: 3%">End Date</th>
            <th class="col" style="width: 8%; text-wrap: normal">Website</th>
            <th class="col" style="width: 3%">Location</th>
            <th class="col" style="width: 3%">Sanctioning Bodies</th>
            <th class="col" style="width: 2%">Status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($meetLists as $key => $meetList)
            <tr>
                <td class="col">
                    {{ $loop->iteration  }}
                </td>
                <td class="col">
                    {{ $meetList->name }}
                </td>
                <td class="col">
                    {{ $meetList->gym->name }}
                </td>
                <td class="col">
                    {{ \Illuminate\Support\Carbon::parse($meetList->start_date)->format('d-m-Y') }}
                </td>
                <td class="col">
                    {{ \Illuminate\Support\Carbon::parse($meetList->end_date)->format('d-m-Y') }}
                </td>
                <td class="col">
                    {{ $meetList->website }}
                </td>
                <td class="col">
                    {{ $meetList->venue_state->name }}, {{ $meetList->venue_state->code }}
                </td>
                <td class="col">
                    @foreach($meetList->sanctionBodies as $key => $sanction)  {{$loop->first?'':', '}} {{$sanction }}  @endforeach
                </td>
                <td class="col">
                    {{ \App\Models\Meet::STATUS_ARRAY[$meetList->registrationStatus] }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <br>
@endif
</body>
</html>
