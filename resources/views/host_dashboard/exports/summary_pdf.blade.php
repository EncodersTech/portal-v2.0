<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'AllGymnastics') }}</title>
    @include('PDF.styles.reboot')
    @include('PDF.styles.reports')
</head>
<body>
    <div class="header">
        <div class="header-text">
            <h1 class="mb-0">
                Dashboard Summary Report
            </h1>
            <h2 class="mb-0">
                Gym: {{ $current_gym->name }}
                <br/>
                Host: {{ $current_gym->user->fullName() }}
            </h2>
            <h4 class="mb-0">
                Date: {{ date("Y-m-d") }}
            </h4>
        </div>
        <div class="logo-container">
            @include('PDF.host.meet.reports.common_logo_image')
        </div>
    </div>
    <table  class="table-1">
    <tr>
        <td colspan="8" style="text-align:center; background-color:gray;"><b>Lifetime Totals</b></td>
    </tr>
    <tr>
        <th colspan="2">Money Earned</th>
        <th colspan="2">Total Athletes</th>
        <th colspan="2">Total Coaches</th>
        <th colspan="2">Total Gyms</th>
    </tr>
    <tr>
        <td colspan="2">{{ number_format($summaryData['total_earn'], 2) }}</td>
        <td colspan="2">{{ $summaryData['total_ath'] }}</td>
        <td colspan="2">{{ $summaryData['total_coa'] }}</td>
        <td colspan="2">{{ $summaryData['total_gym'] }}</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align:center; background-color:gray;"><b>Current (<?= date('Y') ?>) Year Totals</b></td>
    </tr>
    <tr>
        <td colspan="2">{{ number_format($summaryDataThisYear['total_earn'], 2) }}</td>
        <td colspan="2">{{ $summaryDataThisYear['total_ath'] }}</td>
        <td colspan="2">{{ $summaryDataThisYear['total_coa'] }}</td>
        <td colspan="2">{{ $summaryDataThisYear['total_gym'] }}</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align:center; background-color:gray;"><b>Meet List</b></td>
    </tr>
    <tr>
        <th>Meet Name</th>
        <th>Meet Id</th>
        <th>Athlete Count</th>
        <th>Coach Count</th>
        <th>Gym Count</th>
        <th>Total Revenue</th>
        <th>AllGym Fees</th>
        <th>Total</th>
    </tr>
    @foreach($meetSummary as $meet)
        <tr>
            <td>{{ $meet['name'] }}</td>
            <td>{{ $meet['id'] }}</td>
            <td>{{ $meet['data']['total_ath'] }}</td>
            <td>{{ $meet['data']['total_coa'] }}</td>
            <td>{{ $meet['data']['total_gym'] }}</td>
            <td>{{ number_format($meet['data']['total_earn'],2) }}</td>
            <td>{{ number_format($meet['data']['allgym_fees'],2) }}</td>
            <td>{{ number_format($meet['data']['allgym_fees'] + $meet['data']['total_earn'],2)  }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>